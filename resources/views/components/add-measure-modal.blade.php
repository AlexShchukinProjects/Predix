{{--
  Модальное окно «Добавление мероприятия» (дизайн-система).
  Как на вкладке Мероприятия в Safety Reporting: Мероприятие, Срок, Ответственный, Подтверждающий.
  Переменные: $modalId, $formId, $descriptionId, $deadlineId, $responsibleId, $confirmingId, $saveBtnId, $users
--}}
@php
  $modalId = $modalId ?? 'addMeasureModal';
  $formId = $formId ?? 'addMeasureForm';
  $descriptionId = $descriptionId ?? 'measureDescription';
  $deadlineId = $deadlineId ?? 'measureDeadline';
  $responsibleId = $responsibleId ?? 'measureResponsible';
  $confirmingId = $confirmingId ?? 'measureConfirming';
  $saveBtnId = $saveBtnId ?? 'saveMeasureBtn';
  $users = $users ?? collect();
@endphp
<div class="modal fade efds-measure-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">Добавление мероприятия</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="{{ $formId }}">
                    <div class="mb-3">
                        <label for="{{ $descriptionId }}" class="form-label">Мероприятие</label>
                        <textarea class="form-control tall" id="{{ $descriptionId }}" name="description" rows="6" placeholder="Опишите мероприятие..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="{{ $deadlineId }}" class="form-label">Срок исполнения</label>
                                <input type="date" class="form-control" id="{{ $deadlineId }}" name="deadline">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="{{ $responsibleId }}" class="form-label">Ответственный исполнитель</label>
                                <select class="form-select" id="{{ $responsibleId }}" name="action_responsible_id">
                                    <option value="">Не выбран</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}{{ isset($u->email) && $u->email ? ' — ' . $u->email : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="{{ $confirmingId }}" class="form-label">Подтверждающий исполнение</label>
                                <select class="form-select" id="{{ $confirmingId }}" name="action_confirming_id">
                                    <option value="">Не выбран</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}{{ isset($u->email) && $u->email ? ' — ' . $u->email : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="{{ $saveBtnId }}">Сохранить</button>
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
