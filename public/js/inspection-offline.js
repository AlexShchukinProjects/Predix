/**
 * Inspection Offline — PWA offline support for checklist fill pages.
 * Stores pending form submissions (including photos) in IndexedDB.
 * Auto-sends when internet connection is restored.
 */
(function (window) {
    'use strict';

    const DB_NAME    = 'inspections_offline_db';
    const STORE_NAME = 'pending_submissions';
    const DB_VERSION = 1;

    let _db = null;

    /* ───────────── IndexedDB helpers ───────────── */

    function openDB() {
        if (_db) return Promise.resolve(_db);
        return new Promise(function (resolve, reject) {
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function (e) {
                var db = e.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    var store = db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                    store.createIndex('by_inspection', 'inspectionId', { unique: false });
                    store.createIndex('by_status',     'status',       { unique: false });
                }
            };
            req.onsuccess = function (e) { _db = e.target.result; resolve(_db); };
            req.onerror   = function (e) { reject(e.target.error); };
        });
    }

    function dbGet(id) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                var req = db.transaction(STORE_NAME, 'readonly').objectStore(STORE_NAME).get(id);
                req.onsuccess = function () { resolve(req.result); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    function dbGetByInspection(inspectionId) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx    = db.transaction(STORE_NAME, 'readonly');
                var index = tx.objectStore(STORE_NAME).index('by_inspection');
                var req   = index.getAll(inspectionId);
                req.onsuccess = function () {
                    resolve((req.result || []).filter(function (r) { return r.status === 'pending'; }));
                };
                req.onerror = function (e) { reject(e.target.error); };
            });
        });
    }

    function dbGetAllPending() {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx    = db.transaction(STORE_NAME, 'readonly');
                var index = tx.objectStore(STORE_NAME).index('by_status');
                var req   = index.getAll('pending');
                req.onsuccess = function () { resolve(req.result || []); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    function dbSave(record) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx    = db.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);

                /* Remove existing pending records for same inspection before adding new */
                var idxReq = store.index('by_inspection').getAll(record.inspectionId);
                idxReq.onsuccess = function () {
                    (idxReq.result || []).forEach(function (r) {
                        if (r.status === 'pending') store.delete(r.id);
                    });
                    var addReq = store.add(record);
                    addReq.onsuccess = function () { resolve(addReq.result); };
                    addReq.onerror   = function (e) { reject(e.target.error); };
                };
                idxReq.onerror = function (e) { reject(e.target.error); };
            });
        });
    }

    function dbMarkSent(id) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx    = db.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);
                var getR  = store.get(id);
                getR.onsuccess = function () {
                    var rec = getR.result;
                    if (rec) {
                        rec.status = 'sent';
                        rec.sentAt = new Date().toISOString();
                        store.put(rec).onsuccess = function () { resolve(); };
                    } else {
                        resolve();
                    }
                };
                getR.onerror = function (e) { reject(e.target.error); };
            });
        });
    }

    /* ───────────── FormData serialization ───────────── */

    function serializeFormData(formData) {
        var result   = { fields: {}, files: {} };
        var promises = [];

        formData.forEach(function (value, key) {
            if (value instanceof File && value.size > 0) {
                promises.push(new Promise(function (resolve) {
                    var reader    = new FileReader();
                    reader.onload = function () {
                        if (!result.files[key]) result.files[key] = [];
                        result.files[key].push({ name: value.name, type: value.type, data: reader.result });
                        resolve();
                    };
                    reader.onerror = function () { resolve(); };
                    reader.readAsDataURL(value);
                }));
            } else if (!(value instanceof File)) {
                if (!result.fields[key]) result.fields[key] = [];
                result.fields[key].push(String(value));
            }
        });

        return Promise.all(promises).then(function () { return result; });
    }

    function deserializeFormData(serialized, freshCsrf) {
        var fd       = new FormData();
        var promises = [];

        Object.keys(serialized.fields || {}).forEach(function (key) {
            serialized.fields[key].forEach(function (v) {
                fd.append(key, key === '_token' && freshCsrf ? freshCsrf : v);
            });
        });

        Object.keys(serialized.files || {}).forEach(function (key) {
            serialized.files[key].forEach(function (f) {
                promises.push(
                    fetch(f.data)
                        .then(function (r) { return r.blob(); })
                        .then(function (blob) { fd.append(key, new File([blob], f.name, { type: f.type })); })
                        .catch(function () {})
                );
            });
        });

        return Promise.all(promises).then(function () { return fd; });
    }

    /* ───────────── Status bar UI ───────────── */

    function getBar() { return document.getElementById('inspection-sync-bar'); }

    function setStatus(type, mainText, subText) {
        var bar = getBar();
        if (!bar) return;

        var icons = { online: '✅', pending: '⏳', sending: '🔄', error: '❌' };

        bar.className     = 'inspection-sync-bar sync-' + type;
        bar.style.display = 'flex';

        var iconEl  = bar.querySelector('.sync-icon');
        var mainEl  = bar.querySelector('.sync-text-main');
        var subEl   = bar.querySelector('.sync-text-sub');
        var retryEl = bar.querySelector('.sync-retry-btn');

        if (iconEl)  iconEl.textContent  = icons[type] || '•';
        if (mainEl)  mainEl.textContent  = mainText;
        if (subEl)   subEl.textContent   = subText || '';
        if (retryEl) retryEl.style.display = (type === 'error') ? 'inline-block' : 'none';
    }

    /* ───────────── Network send ───────────── */

    function sendRecord(record) {
        var freshCsrf = (document.querySelector('meta[name="csrf-token"]') || {}).content;
        return deserializeFormData(record.formData, freshCsrf).then(function (fd) {
            return fetch(record.url, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': freshCsrf || '', 'Accept': 'application/json' },
                body:    fd
            });
        }).then(function (response) {
            if (response.status === 419) return Promise.reject(new Error('CSRF_EXPIRED'));
            var ct = response.headers.get('content-type') || '';
            if (!ct.includes('application/json')) return Promise.reject(new Error('Неверный ответ сервера'));
            return response.json();
        }).then(function (data) {
            if (!data || !data.success) return Promise.reject(new Error(data && data.message ? data.message : 'Ошибка сервера'));
            return data;
        });
    }

    function flushForInspection(inspectionId, onSuccess) {
        return dbGetByInspection(inspectionId).then(function (pending) {
            if (!pending.length) return;
            setStatus('sending', 'Отправка данных на сервер…', '');

            var chain = Promise.resolve();
            pending.forEach(function (record) {
                chain = chain.then(function () {
                    return sendRecord(record)
                        .then(function (data) {
                            return dbMarkSent(record.id).then(function () {
                                var now = new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
                                setStatus('online', 'Отправлен на сервер', 'Синхронизировано в ' + now);
                                if (typeof onSuccess === 'function') onSuccess(data);
                            });
                        })
                        .catch(function (err) {
                            if (err.message === 'CSRF_EXPIRED') {
                                setStatus('error', 'Сессия истекла', 'Обновите страницу и отправьте снова');
                            } else {
                                setStatus('error', 'Ошибка отправки', err.message);
                            }
                        });
                });
            });
            return chain;
        });
    }

    /* ───────────── Fill-page init ───────────── */

    function initFillPage(inspectionId) {
        /* Check for existing pending submission */
        dbGetByInspection(inspectionId)
            .then(function (pending) {
                if (pending.length > 0) {
                    var t = new Date(pending[0].savedAt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
                    setStatus('pending', 'Ожидает отправки', 'Сохранено в ' + t + ' (нет подключения)');

                    if (navigator.onLine) {
                        flushForInspection(inspectionId, function (data) {
                            if (data && data.redirect_url) {
                                setTimeout(function () { window.location.href = data.redirect_url; }, 1200);
                            }
                        });
                    }
                } else {
                    var bar = getBar();
                    if (bar) bar.style.display = 'none';
                }
            })
            .catch(function () {});

        /* Auto-flush on reconnect */
        window.addEventListener('online', function () {
            flushForInspection(inspectionId, function (data) {
                if (data && data.redirect_url) {
                    setTimeout(function () { window.location.href = data.redirect_url; }, 1200);
                }
            });
        });

        /* Override global submit handler */
        window._inspectionFormSubmit = function (ev, formEl) {
            if (ev) { ev.preventDefault(); ev.stopPropagation(); }

            if (!navigator.onLine) {
                /* ── OFFLINE path ── */
                setStatus('sending', 'Сохранение данных локально…', '');
                serializeFormData(new FormData(formEl))
                    .then(function (serialized) {
                        return dbSave({
                            inspectionId: inspectionId,
                            url:          formEl.action,
                            formData:     serialized,
                            status:       'pending',
                            savedAt:      new Date().toISOString(),
                            sentAt:       null,
                            attempts:     0
                        });
                    })
                    .then(function () {
                        var t = new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
                        setStatus('pending', 'Ожидает отправки', 'Сохранено в ' + t + '. Отправится при подключении к сети.');
                        if (window.showNotification) window.showNotification('Нет сети. Данные сохранены — отправятся автоматически.', 'success');
                    })
                    .catch(function (err) {
                        setStatus('error', 'Не удалось сохранить', err && err.message ? err.message : 'Ошибка');
                        if (window.showNotification) window.showNotification('Ошибка сохранения: ' + (err && err.message ? err.message : ''), 'error');
                    });
                return;
            }

            /* ── ONLINE path ── */
            setStatus('sending', 'Отправка на сервер…', '');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            fetch(formEl.action, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '', 'Accept': 'application/json' },
                body:    new FormData(formEl)
            })
                .then(function (r) {
                    var ct = r.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) return r.text().then(function () { return Promise.reject(new Error('Неверный ответ сервера')); });
                    return r.json();
                })
                .then(function (data) {
                    if (data && data.success) {
                        var now = new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
                        setStatus('online', 'Отправлен на сервер', 'Сохранено в ' + now);
                        if (window.showNotification) window.showNotification(data.message || 'Ответы сохранены.', 'success');
                        setTimeout(function () {
                            window.location.href = data.redirect_url || '/modules/inspections/active';
                        }, 800);
                    } else {
                        setStatus('error', 'Ошибка отправки', data && data.message ? data.message : 'Неизвестная ошибка');
                        if (window.showNotification) window.showNotification(data && data.message ? data.message : 'Ошибка', 'error');
                    }
                })
                .catch(function (err) {
                    setStatus('error', 'Ошибка отправки', err && err.message ? err.message : '');
                    if (window.showNotification) window.showNotification(err && err.message ? err.message : 'Ошибка при сохранении', 'error');
                });
        };

        /* Retry button */
        var bar = getBar();
        if (bar) {
            bar.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('sync-retry-btn')) {
                    flushForInspection(inspectionId, function (data) {
                        if (data && data.redirect_url) {
                            setTimeout(function () { window.location.href = data.redirect_url; }, 1000);
                        }
                    });
                }
            });
        }
    }

    /* ───────────── Active-page card badges ───────────── */

    function checkActiveCards() {
        dbGetAllPending()
            .then(function (all) {
                all.forEach(function (record) {
                    var cards = document.querySelectorAll('[data-inspection-id="' + record.inspectionId + '"]');
                    cards.forEach(function (card) {
                        var badge = card.querySelector('.sync-status-badge');
                        if (!badge) return;
                        var t = new Date(record.savedAt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
                        badge.textContent    = '⏳ Ожидает отправки (' + t + ')';
                        badge.className      = 'sync-status-badge badge-sync-pending';
                        badge.style.display  = 'inline-flex';
                    });
                });

                /* Flush if online */
                if (navigator.onLine && all.length > 0) {
                    var chain = Promise.resolve();
                    all.forEach(function (record) {
                        chain = chain.then(function () {
                            return sendRecord(record)
                                .then(function () {
                                    return dbMarkSent(record.id).then(function () {
                                        var cards = document.querySelectorAll('[data-inspection-id="' + record.inspectionId + '"]');
                                        cards.forEach(function (card) {
                                            var badge = card.querySelector('.sync-status-badge');
                                            if (!badge) return;
                                            badge.textContent   = '✅ Отправлен на сервер';
                                            badge.className     = 'sync-status-badge badge-sync-sent';
                                            badge.style.display = 'inline-flex';
                                        });
                                    });
                                })
                                .catch(function () {});
                        });
                    });
                }
            })
            .catch(function () {});
    }

    /* ───────────── Public API ───────────── */

    window.InspectionOffline = {
        initFillPage:     initFillPage,
        checkActiveCards: checkActiveCards,
        setStatus:        setStatus,
        flushForInspection: flushForInspection
    };

}(window));
