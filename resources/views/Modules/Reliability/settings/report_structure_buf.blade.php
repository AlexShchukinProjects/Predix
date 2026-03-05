@extends('layout.main')

@section('content')
<div class="container-fluid mt-3">
    <h4 class="mb-3">Структура отчёта BUF</h4>

    @if(session('success'))
        <div class="alert alert-success py-1 px-2 mb-2">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body p-2">
            <form method="POST" action="{{ route('modules.reliability.settings.report-structure-buf.update') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">Параметр</th>
                            <th style="width: 30%;">Настройки</th>
                            <th style="width: 25%;">Пример (вывод на печать)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1. № записи (начиная с …)</td>
                            <td>
                                <input
                                    type="number"
                                    name="start_number_prefix"
                                    class="form-control form-control-sm"
                                    min="1"
                                    value="{{ old('start_number_prefix', $bufSetting->start_number_prefix) }}"
                                >
                            </td>
                            <td>{{ $bufSetting->start_number_prefix }}51788</td>
                        </tr>
                        <tr>
                            <td>2. Дата обнаружения</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="ДД.ММ.ГГГГ">
                            </td>
                            <td>05.04.2025</td>
                        </tr>
                        <tr>
                            <td>3. Регистрационный номер ВС</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Номер борта (например, 06012)">
                            </td>
                            <td>06012</td>
                        </tr>
                        <tr>
                            <td>4. Модификация типа ВС</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Код модификации (например, 7600)">
                            </td>
                            <td>7600</td>
                        </tr>
                        <tr>
                            <td>5. «00»</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="00">
                            </td>
                            <td>00</td>
                        </tr>
                        <tr>
                            <td>6. Проявление неисправности ВС</td>
                            <td>
                                <textarea class="form-control form-control-sm" rows="2" placeholder="Краткое описание проявления неисправности"></textarea>
                            </td>
                            <td>механическое повреждение антенны CI-205-3 из комплекта KN-62А №29057</td>
                        </tr>
                        <tr>
                            <td>7. Этап обнаружения отказа (на земле 10, в полете 20)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="10 или 20">
                            </td>
                            <td>10</td>
                        </tr>
                        <tr>
                            <td>8. Функциональная система/подсистема</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Код системы/подсистемы (например, 110122)">
                            </td>
                            <td>110122</td>
                        </tr>
                        <tr>
                            <td>9. Причина неисправности КИ</td>
                            <td>
                                <textarea class="form-control form-control-sm" rows="2" placeholder="Причина неисправности КИ"></textarea>
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>10. Тип КИ</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Тип КИ">
                            </td>
                            <td>CI-205-3</td>
                        </tr>
                        <tr>
                            <td>11. Зав. № КИ</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Заводской номер">
                            </td>
                            <td>441794</td>
                        </tr>
                        <tr>
                            <td>12. Наработка КИ СНЭ (ч.)</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="Часы СНЭ">
                            </td>
                            <td>5951.2</td>
                        </tr>
                        <tr>
                            <td>13. Наработка КИ ППР (ч.)</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="Часы ППР">
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>14. Ед. наработки (всегда прочерк)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="-" readonly>
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>15. Наработка ВС в часах</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="Часы налёта ВС">
                            </td>
                            <td>3333.1</td>
                        </tr>
                        <tr>
                            <td>16. Наработка ВС в посадках</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="Посадки ВС">
                            </td>
                            <td>1785</td>
                        </tr>
                        <tr>
                            <td>17. — (всегда прочерк)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="-" readonly>
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>18. — (всегда прочерк)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="-" readonly>
                            </td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-2 text-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        Сохранить префикс
                    </button>
                </div>
                <div class="mt-1 text-muted" style="font-size: 0.8rem;">
                    Итоговый номер записи = префикс (из колонки «Настройки») + ID отказа, например: {{ $bufSetting->start_number_prefix }} + 51788 → {{ $bufSetting->start_number_prefix }}51788.
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


