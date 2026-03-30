@php
    $sortColumn = $sortColumn ?? 'id';
    $sortDirection = $sortDirection ?? 'asc';
    $sortTh = 'Modules.Reliability.settings.master_data.partials.sort_th';
    $pairs = [
        ['id', 'id'],
        ['eef_number', 'EEF Number'],
        ['nrc_number', 'NRC Number'],
        ['ac_type', 'AC Type'],
        ['ata', 'ATA'],
        ['project_no', 'Project No.'],
        ['subject', 'Subject'],
        ['remarks', 'Remarks'],
        ['location', 'Location'],
        ['eef_status', 'EEF Status'],
        ['link', 'Link'],
        ['link_path', 'Link Path'],
        ['man_hours', 'Man Hours'],
        ['chargeable_to_customer', 'Chargeable to Customer?'],
        ['customer_name', 'Customer Name'],
        ['inspection_source_task', 'Inspection Source Task'],
        ['rc_number', 'RC#'],
        ['open_date', 'Open Date'],
        ['assigned_engineering_engineer', 'Assigned Engineering Engineer'],
        ['open_continuation_raised_by_production_dates', 'OPEN/Continuation Raised by Production Dates'],
        ['answer_provided_by_engineering_dates', 'Answer provided by Engineering Dates'],
        ['oem_communication_reference', 'OEM Communication Reference'],
        ['gaes_eo', 'GAES EO'],
        ['manual_limits_out_within', 'Manual limits (OUT / WITHIN)'],
        ['backup_engineer', 'Back-up Engineer'],
        ['project_status', 'Project Status'],
        ['eef_priority', 'EEF Priority'],
        ['latest_processing', 'Latest Processing'],
        ['project_status2', 'Project Status2'],
        ['eef_with', 'EEF with'],
        ['standard_remarks_on_current_progress', 'Standard remarks (progress)'],
        ['latest_comments_short_answer', 'Latest comments / short answer'],
        ['project_status3', 'Project Status3'],
        ['created_at', 'created_at'],
        ['updated_at', 'updated_at'],
    ];
@endphp
@foreach ($pairs as [$col, $label])
    @include($sortTh, ['column' => $col, 'label' => $label, 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
@endforeach
