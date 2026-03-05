<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Отправить сообщение
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer|exists:chats,id',
            'message' => 'nullable|string|max:5000|required_without:attachment',
            'attachment' => 'nullable|file|max:20480|required_without:message',
            'reply_to_message_id' => 'nullable|integer|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        $chatId = $request->input('chat_id');
        $replyToMessageId = $request->input('reply_to_message_id');

        // Проверяем, является ли пользователь участником чата
        $chat = Chat::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->find($chatId);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату',
            ], 403);
        }

        // Если указан reply_to_message_id, проверяем, что сообщение принадлежит этому чату
        if ($replyToMessageId) {
            $replyToMessage = ChatMessage::where('chat_id', $chatId)
                ->where('id', $replyToMessageId)
                ->first();
            
            if (!$replyToMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сообщение, на которое отвечают, не найдено в этом чате',
                ], 404);
            }
        }

        try {
            $attachmentData = [
                'attachment_path' => null,
                'attachment_original_name' => null,
                'attachment_mime' => null,
                'attachment_size' => null,
                'is_image' => false,
            ];

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('chat_attachments', 'public');

                $attachmentData = [
                    'attachment_path' => $path,
                    'attachment_original_name' => $file->getClientOriginalName(),
                    'attachment_mime' => $file->getClientMimeType(),
                    'attachment_size' => $file->getSize(),
                    'is_image' => str_starts_with($file->getClientMimeType(), 'image/'),
                ];
            }

            $message = ChatMessage::create([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'message' => $request->input('message', '') ?? '',
                'reply_to_message_id' => $replyToMessageId,
                ...$attachmentData,
            ]);

            $message->load(['user', 'replyTo.user']);

            // Для нового сообщения is_read всегда false
            $responseData = [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'user_name' => $message->user->name,
                'message' => $message->message,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'is_read' => false,
                'attachment_url' => $message->attachment_path ? asset('storage/' . $message->attachment_path) : null,
                'attachment_name' => $message->attachment_original_name,
                'attachment_mime' => $message->attachment_mime,
                'attachment_size' => $message->attachment_size,
                'is_image' => (bool) $message->is_image,
            ];

            // Добавляем информацию об ответе, если есть
            if ($message->replyTo) {
                $responseData['reply_to'] = [
                    'id' => $message->replyTo->id,
                    'user_name' => $message->replyTo->user->name,
                    'message' => $message->replyTo->message,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => $responseData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке сообщения: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить новые сообщения (для polling)
     */
    public function getNewMessages(Request $request, int $chatId): JsonResponse
    {
        $userId = Auth::id();

        // Проверяем, является ли пользователь участником чата
        $chat = Chat::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->find($chatId);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату',
            ], 403);
        }

        // Получаем информацию о прочтении для всех участников
        $participants = $chat->participants()->with('user')->get();
        $participantsReadAt = [];
        foreach ($participants as $participant) {
            $participantsReadAt[$participant->user_id] = $participant->last_read_at;
        }

        $lastMessageId = $request->input('lastMessageId', 0);

        $messages = ChatMessage::where('chat_id', $chatId)
            ->where('id', '>', $lastMessageId)
            ->with(['user', 'replyTo.user'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($userId, $chat, $participantsReadAt) {
                $isRead = $this->isMessageRead($message, $userId, $chat, $participantsReadAt);
                
                $messageData = [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user->name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'is_read' => $isRead,
                    'attachment_url' => $message->attachment_path ? asset('storage/' . $message->attachment_path) : null,
                    'attachment_name' => $message->attachment_original_name,
                    'attachment_mime' => $message->attachment_mime,
                    'attachment_size' => $message->attachment_size,
                    'is_image' => (bool) $message->is_image,
                ];

                // Добавляем информацию об ответе, если есть
                if ($message->replyTo) {
                    $messageData['reply_to'] = [
                        'id' => $message->replyTo->id,
                        'user_name' => $message->replyTo->user->name,
                        'message' => $message->replyTo->message,
                    ];
                }

                return $messageData;
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Проверить, прочитано ли сообщение
     */
    private function isMessageRead($message, int $currentUserId, $chat, array $participantsReadAt): bool
    {
        // Если сообщение отправил текущий пользователь, проверяем прочтение другими
        if ($message->user_id === $currentUserId) {
            if ($chat->isPrivate()) {
                // Для приватных чатов: проверяем, прочитал ли другой участник
                foreach ($participantsReadAt as $participantId => $lastReadAt) {
                    if ($participantId !== $currentUserId && $lastReadAt) {
                        return $lastReadAt >= $message->created_at;
                    }
                }
                return false;
            } else {
                // Для групповых чатов: проверяем, прочитали ли все остальные участники
                $otherParticipants = array_filter($participantsReadAt, function ($participantId) use ($currentUserId) {
                    return $participantId !== $currentUserId;
                }, ARRAY_FILTER_USE_KEY);
                
                if (empty($otherParticipants)) {
                    return false;
                }
                
                foreach ($otherParticipants as $lastReadAt) {
                    if (!$lastReadAt || $lastReadAt < $message->created_at) {
                        return false;
                    }
                }
                return true;
            }
        }
        
        // Если сообщение не от текущего пользователя, всегда считаем прочитанным (т.к. он его видит)
        return true;
    }

    /**
     * Отметить сообщения как прочитанные
     */
    public function markAsRead(int $chatId): JsonResponse
    {
        $userId = Auth::id();

        $participant = ChatParticipant::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату',
            ], 403);
        }

        try {
            $participant->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Сообщения отмечены как прочитанные',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении статуса: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Редактировать сообщение
     */
    public function update(Request $request, int $messageId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . $validator->errors()->first('message'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        $message = ChatMessage::find($messageId);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Сообщение не найдено',
            ], 404);
        }

        // Проверяем, что сообщение принадлежит текущему пользователю
        if ($message->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Нет прав на редактирование этого сообщения',
            ], 403);
        }

        try {
            $message->update([
                'message' => $request->input('message'),
            ]);

            $message->load('user');

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user->name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при редактировании сообщения', [
                'message_id' => $messageId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при редактировании сообщения: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Удалить сообщение
     */
    public function destroy(int $messageId): JsonResponse
    {
        $userId = Auth::id();
        $message = ChatMessage::findOrFail($messageId);

        // Проверяем, что сообщение принадлежит текущему пользователю
        if ($message->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Нет прав на удаление этого сообщения',
            ], 403);
        }

        try {
            // Удаляем файл вложения, если он есть
            if ($message->attachment_path) {
                try {
                    \Storage::disk('public')->delete($message->attachment_path);
                } catch (\Throwable $e) {
                    // Логируем, но не прерываем удаление сообщения
                    \Log::warning('Не удалось удалить файл вложения сообщения', [
                        'message_id' => $messageId,
                        'path' => $message->attachment_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Сообщение удалено',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении сообщения: ' . $e->getMessage(),
            ], 500);
        }
    }
}
