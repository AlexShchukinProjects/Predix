<style>
/* Кнопка "Назад" как на /settings/modules */
.back-button {
    color: #007bff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.3s ease;
    border: none;
    background: none;
    padding: 8px 0;
}
.back-button:hover {
    color: #0056b3;
    text-decoration: none;
}
.back-button i {
    font-size: 14px;
}

.main_screen {
    width: 100% !important;
}
.inspection-settings-table-card {
    width: 100%;
    overflow: hidden;
}
.inspection-settings-table-card .card,
.inspection-settings-table-card .card-body {
    min-width: 0;
    overflow: hidden;
}
.inspection-settings-table-card .reliability-table-scroll-wrap {
    width: 100%;
    min-width: 0;
    height: calc(100vh - 320px);
    min-height: 300px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.inspection-settings-table-card .reliability-table-scroll-wrap .table-responsive {
    width: 100%;
    flex: 1;
    min-height: 0;
    min-width: 0;
    overflow-x: auto;
    overflow-y: auto;
}
.inspection-settings-table-card .table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8f9fa;
    box-shadow: 0 1px 0 0 #dee2e6;
}
.inspection-settings-table-card .table thead th::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 1px;
    background: #dee2e6;
}
.efds-pagination-wrap {
    margin-top: 1rem !important;
    padding-top: 0.75rem;
}
.efds-table-header__stats {
    white-space: nowrap;
    flex-shrink: 0;
    min-width: 280px;
}
.inspection-upload-dropzone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background-color 0.2s;
}
.inspection-upload-dropzone:hover,
.inspection-upload-dropzone.drag-over {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}
</style>
