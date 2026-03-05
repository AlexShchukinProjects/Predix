<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Главная страница чата
     */
    public function index(): View
    {
        return view('Chat.index');
    }

    /**
     * Получить общее количество непрочитанных сообщений
     */
    public function getUnreadCount(): JsonResponse
    {
        $userId = Auth::id();

        $unreadCount = ChatMessage::whereHas('chat.participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('user_id', '!=', $userId) // Только сообщения от других пользователей
            ->whereDoesntHave('chat.participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereColumn('last_read_at', '>=', 'messages.created_at');
            })
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Получить список чатов пользователя
     */
    public function getChats(): JsonResponse
    {
        $userId = Auth::id();

        $chats = Chat::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['participants.user', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId)
                    ->whereDoesntHave('chat.participants', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->whereColumn('last_read_at', '>=', 'messages.created_at');
                    });
            }])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($chat) use ($userId) {
                $lastMessage = $chat->messages->first();
                $otherParticipant = $chat->getOtherParticipant($userId);
                
                return [
                    'id' => $chat->id,
                    'type' => $chat->type,
                    'name' => $chat->getDisplayName($userId),
                    'last_message' => $lastMessage ? [
                        'message' => $lastMessage->message,
                        'created_at' => $lastMessage->created_at->format('Y-m-d H:i:s'),
                        'user_name' => $lastMessage->user->name,
                        'attachment_name' => $lastMessage->attachment_original_name,
                        'is_image' => (bool) $lastMessage->is_image,
                    ] : null,
                    'unread_count' => $chat->unread_count ?? 0,
                    'updated_at' => $chat->updated_at->format('Y-m-d H:i:s'),
                    'other_participant' => $otherParticipant ? [
                        'id' => $otherParticipant->id,
                        'name' => $otherParticipant->name,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'chats' => $chats,
        ]);
    }

    /**
     * Получить или создать приватный чат с пользователем
     */
    public function getOrCreatePrivateChat(int $userId): JsonResponse
    {
        $currentUserId = Auth::id();

        if ($currentUserId === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя создать чат с самим собой',
            ], 400);
        }

        // Проверяем, существует ли уже приватный чат между этими пользователями
        $existingChat = Chat::where('type', 'private')
            ->whereHas('participants', function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            })
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();

        if ($existingChat) {
            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $existingChat->id,
                    'type' => $existingChat->type,
                    'name' => $existingChat->getDisplayName($currentUserId),
                ],
            ]);
        }

        // Создаем новый приватный чат
        DB::beginTransaction();
        try {
            $chat = Chat::create([
                'type' => 'private',
                'created_by' => $currentUserId,
            ]);

            // Добавляем обоих участников
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $currentUserId,
                'joined_at' => now(),
            ]);

            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $userId,
                'joined_at' => now(),
            ]);

            DB::commit();

            $otherUser = User::find($userId);

            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'type' => $chat->type,
                    'name' => $otherUser->name ?? 'Пользователь',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании чата: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Создать групповой чат
     */
    public function createGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'integer|exists:users,id',
        ]);

        $currentUserId = Auth::id();

        // Проверяем, что текущий пользователь включен в участники
        if (!in_array($currentUserId, $validated['participants'])) {
            $validated['participants'][] = $currentUserId;
        }

        DB::beginTransaction();
        try {
            $chat = Chat::create([
                'type' => 'group',
                'name' => $validated['name'],
                'created_by' => $currentUserId,
            ]);

            // Добавляем всех участников
            foreach ($validated['participants'] as $participantId) {
                ChatParticipant::create([
                    'chat_id' => $chat->id,
                    'user_id' => $participantId,
                    'joined_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'type' => $chat->type,
                    'name' => $chat->name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании группы: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Добавить участников в группу
     */
    public function addParticipants(Request $request, int $chatId): JsonResponse
    {
        $validated = $request->validate([
            'participants' => 'required|array|min:1',
            'participants.*' => 'integer|exists:users,id',
        ]);

        $chat = Chat::findOrFail($chatId);

        // Проверяем права доступа
        if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату',
            ], 403);
        }

        if (!$chat->isGroup()) {
            return response()->json([
                'success' => false,
                'message' => 'Можно добавлять участников только в групповые чаты',
            ], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($validated['participants'] as $participantId) {
                // Проверяем, не является ли пользователь уже участником
                if (!$chat->participants()->where('user_id', $participantId)->exists()) {
                    ChatParticipant::create([
                        'chat_id' => $chat->id,
                        'user_id' => $participantId,
                        'joined_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Участники добавлены',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении участников: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить участников группы
     */
    public function getParticipants(int $chatId): JsonResponse
    {
        $userId = Auth::id();

        $chat = Chat::findOrFail($chatId);

        // Проверяем права доступа
        if (!$chat->participants()->where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату',
            ], 403);
        }

        if (!$chat->isGroup()) {
            return response()->json([
                'success' => false,
                'message' => 'Это не групповая беседа',
            ], 400);
        }

        $participants = $chat->participants()
            ->with('user')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->user_id,
                    'name' => $participant->user->name,
                    'email' => $participant->user->email,
                    'joined_at' => $participant->joined_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'participants' => $participants,
        ]);
    }

    /**
     * Удалить участника из группы
     */
    public function removeParticipant(Request $request, int $chatId): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = Auth::id();
        $chat = Chat::findOrFail($chatId);

        // Проверяем права доступа
        if (!$chat->participants()->where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату',
            ], 403);
        }

        if (!$chat->isGroup()) {
            return response()->json([
                'success' => false,
                'message' => 'Можно удалять участников только из групповых чатов',
            ], 400);
        }

        // Нельзя удалить самого себя
        if ($validated['user_id'] === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить самого себя из группы',
            ], 400);
        }

        try {
            $participant = $chat->participants()
                ->where('user_id', $validated['user_id'])
                ->first();

            if ($participant) {
                $participant->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Участник удален',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Участник не найден',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении участника: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить сообщения чата
     */
    public function getChatMessages(int $chatId): JsonResponse
    {
        $userId = Auth::id();

        // Проверяем, является ли пользователь участником чата
        $chat = Chat::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->findOrFail($chatId);

        // Получаем информацию о прочтении для всех участников
        $participants = $chat->participants()->with('user')->get();
        $participantsReadAt = [];
        foreach ($participants as $participant) {
            $participantsReadAt[$participant->user_id] = $participant->last_read_at;
        }

        $messages = ChatMessage::where('chat_id', $chatId)
            ->with(['user', 'replyTo.user'])
            ->orderBy('created_at', 'asc')
            ->limit(100)
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
            'chat' => [
                'id' => $chat->id,
                'type' => $chat->type,
                'name' => $chat->getDisplayName($userId),
                'participants_count' => $chat->participants()->count(),
                'created_by' => $chat->created_by,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Проверить, прочитано ли сообщение
     */
    private function isMessageRead($message, int $currentUserId, Chat $chat, array $participantsReadAt): bool
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
     * Поиск пользователей
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $currentUserId = Auth::id();

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'users' => [],
            ]);
        }

        $users = User::where('id', '!=', $currentUserId)
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('login', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Удалить группу
     */
    public function deleteGroup(int $chatId): JsonResponse
    {
        $userId = Auth::id();

        $chat = Chat::findOrFail($chatId);

        // Проверяем, что это групповая беседа
        if ($chat->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Можно удалить только групповую беседу',
            ], 400);
        }

        // Проверяем, что пользователь является участником группы
        $participant = $chat->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не являетесь участником этой группы',
            ], 403);
        }

        // Проверяем, что пользователь является создателем группы
        if ($chat->created_by !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Только создатель группы может удалить её',
            ], 403);
        }

        // Удаляем группу (все связанные данные удалятся каскадно)
        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Группа успешно удалена',
        ]);
    }

    /**
     * Покинуть группу
     */
    public function leaveGroup(int $chatId): JsonResponse
    {
        $userId = Auth::id();

        $chat = Chat::findOrFail($chatId);

        // Проверяем, что это групповая беседа
        if ($chat->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Можно покинуть только групповую беседу',
            ], 400);
        }

        // Проверяем, что пользователь является участником группы
        $participant = $chat->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не являетесь участником этой группы',
            ], 403);
        }

        // Проверяем, что пользователь не является создателем группы
        if ($chat->created_by === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Создатель группы не может покинуть её. Используйте функцию "Удалить группу"',
            ], 400);
        }

        // Удаляем участника из группы
        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Вы покинули группу',
        ]);
    }
}