/**
 * Crew Schedule Chart - JavaScript модуль для отрисовки таблицы расписания экипажа
 * 
 * Этот файл отвечает за:
 * - Отрисовку таблицы экипажа на canvas2
 * - Управление фильтрами должностей сотрудников
 * - Отображение статусов сотрудников (цветные круги)
 * - Обработку событий для назначения мероприятий экипажу
 * - Управление модальными окнами для работы с экипажем
 * - Интеграцию с системой мероприятий и рейсов
 */

import 'bootstrap/dist/js/bootstrap.bundle.js';

// Функция для получения выбранных должностей из localStorage
function getSelectedPositionsFromStorage() {
    try {
        // Предпочтительно берем ключ Crewplan, при отсутствии — ключ Schedule
        const crewplanSaved = localStorage.getItem('crewplan_positionFilterSelection');
        const scheduleSaved = localStorage.getItem('positionFilterSelection_schedule');
        const raw = crewplanSaved ?? scheduleSaved ?? '[]';
        const saved = JSON.parse(raw);
        return Array.isArray(saved) ? saved : [];
    } catch (e) {
        console.error('Error parsing positions from localStorage:', e);
        return [];
    }
}

// Глобальные переменные для отфильтрованных сотрудников
let filteredMembers = [];
let filteredMemberNames = [];
let filteredMemberPositions = [];
let filteredMemberIds = [];

// Глобальные переменные для статусов сотрудников
let crewStatusData = [];
let crewStatusMap = {};

let variant=0;
let id;
let aircraft;
let flight_number;
let arrival_airport;
let departure_airport;
let time_start;
let time_finish;
let actual_time_departure;
let actual_time_arrival;    
let status;
let trip_number;
let passengers_count;
let checklist_completed;
let activity_type;

// Глобальные переменные для мероприятий
let event_id;
let crew_id;
let crew_name;
let crew_position;
let flight_type;
let event_type_name;

let hoveredObj;
let ClickCanvas=document.getElementById("canvas2");



let show_flight = document.getElementById('show_flight');
show_flight.addEventListener("change", drawChange);

let show_measures = document.getElementById('show_measures');
show_measures.addEventListener("change", drawChange);

let show_maintenance = document.getElementById('show_maintenance');
show_maintenance.addEventListener("change", drawChange);


let x1stroke;
let y1stroke;
let XLongStroke;
let heightRectFlight1stroke;


function drawChange()
{
 // console.log("drawChange");
cleanCanvas()
drawTable2()
window.rectHoverCrew = rectHover; // Обновляем после отрисовки

}


const data = JSON.parse(document.getElementById('data').dataset.maps);
// console.log(data, "databottom");

const MemberJson = JSON.parse(document.getElementById('MemberJson').dataset.maps);

// Загружаем статусы сотрудников
loadCrewStatusData().then(() => {
    // Перерисовываем canvas после загрузки статусов
    if (typeof drawTable2 === 'function') {
        drawTable2();
        window.rectHoverCrew = rectHover; // Обновляем после отрисовки
    }
});

// Функция для загрузки статусов сотрудников
async function loadCrewStatusData() {
    try {
        const response = await fetch('/api/crew-status', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                crewStatusData = result.data;
                // Создаем карту для быстрого поиска по ID
                crewStatusMap = {};
                crewStatusData.forEach(crew => {
                    crewStatusMap[crew.id] = crew;
                });
                console.log('Статусы сотрудников загружены:', crewStatusData);
                return true;
            } else {
                console.error('Ошибка загрузки статусов:', result.message);
                return false;
            }
        } else {
            console.error('Ошибка HTTP:', response.status);
            return false;
        }
    } catch (error) {
        console.error('Ошибка при загрузке статусов сотрудников:', error);
        return false;
    }
}

// Функция для определения цвета статуса сотрудника
function getCrewStatusColor(crewId) {
    const crewStatus = crewStatusMap[crewId];
    if (!crewStatus) {
        return "#C7C7CC"; // серый по умолчанию
    }
    
    switch (crewStatus.overall_status) {
        case 'expired':
            return "#FF3B30"; // красный
        case 'warning':
            return "#FFCC00"; // желтый
        case 'expiring':
            return "#FF9500"; // оранжевый
        case 'active':
            return "#34C759"; // зеленый
        default:
            return "#C7C7CC"; // серый
    }
}

// Функция для получения текста статуса
function getStatusText(status) {
    switch(status) {
        case 'expired':
            return 'Истек';
        case 'warning':
            return 'Предупреждение';
        case 'expiring':
            return 'Истекает';
        case 'active':
            return 'Активен';
        default:
            return 'Не определен';
    }
}

// Глобальные переменные для canvas оверлеев
let pastTimeCanvas2 = null;
let pastTimeCtx2 = null;
const allAircraftsJson = JSON.parse(document.getElementById('allAircraftsJson').dataset.maps);


// console.log("MemberJson",MemberJson);

// console.log("canvas2",data);

let checked=[];
let longFirstColumn=220;

let date1=document.getElementById("dateQ")
let period=document.getElementById("period")

// Функция для перезагрузки данных
async function reloadCrewData() {
    try {
        const response = await fetch('/planning/crew-schedule-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                dateQ: date1.value,
                period: period.value
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                // Обновляем глобальные данные
                const newData = JSON.parse(result.json);
                // Очищаем старые данные и добавляем новые
                Object.keys(data).forEach(key => delete data[key]);
                Object.assign(data, newData);
                console.log('Данные обновлены:', newData);
                return true;
            }
        }
        console.error('Ошибка загрузки данных');
        return false;
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
        return false;
    }
}

// Добавляем обработчик изменения даты для полной перерисовки canvas
date1.addEventListener('change', async function() {
    console.log('Date changed, reloading data and redrawing canvas');
    const success = await reloadCrewData();
    if (success) {
        cleanCanvas();
        drawTable2();
        window.rectHoverCrew = rectHover; // Обновляем после отрисовки
    }
});

// Добавляем обработчик изменения периода
period.addEventListener('change', async function() {
    console.log('Period changed, reloading data and redrawing canvas');
    const success = await reloadCrewData();
    if (success) {
        cleanCanvas();
        drawTable2();
        window.rectHoverCrew = rectHover; // Обновляем после отрисовки
    }
});



let QuantityFL=Object.keys(data).length;

// console.log("QuantityFL",QuantityFL);

let AircraftsQuantity=Object.keys(allAircraftsJson).length;
let AircraftsNum=[]
for(let i=0; i<AircraftsQuantity; i++)
{
AircraftsNum[i] = allAircraftsJson[i]
}


let MemberQuantity=Object.keys(MemberJson).length;



let MemberName=[];
let MemberId=[];
let MemberPosition=[];
for(let i=0; i<MemberQuantity; i++)
{

    MemberName[i] = MemberJson[i]['name'];

    //console.log(MemberJson, "MemberName");
    
    MemberId[i] = MemberJson[i]['id'];
    MemberPosition[i] = MemberJson[i]['position'];
    
}

// Быстрые словари для поиска по id
const memberIdToName = (() => {
    const map = {};
    for (let i = 0; i < MemberQuantity; i++) {
        const id = MemberId[i];
        if (id != null) map[id] = MemberName[i];
    }
    return map;
})();

// Отображение информации об экипаже в tooltip
function displayCrewInfo(crewData) {
    const el = document.getElementById("crewInfo");
    if (!el) return;

    let html = '';
    let has = false;

    // Массив (мероприятия): [{name, position}]
    if (Array.isArray(crewData)) {
        crewData.forEach(m => {
            if (!m) return;
            const pos = m.position || '';
            const name = m.name || '';
            if (String(name).trim() !== '') {
                html += `<div><strong>${pos ? pos + ':' : ''}</strong> ${name}</div>`;
                has = true;
            }
        });
        el.innerHTML = has ? html : 'Нет назначенных сотрудников';
        return;
    }

    // Объект (рейс): {КВС:{id,name}, ВП:{...}, ...}
    // Отображаем все должности динамически в порядке ключей объекта
    if (crewData && typeof crewData === 'object') {
        Object.keys(crewData).forEach(key => {
            const val = crewData[key];
            if (!val) return;
            const name = typeof val === 'string' ? val : (val.name || '');
            if (String(name).trim() !== '') {
                html += `<div><strong>${key}:</strong> ${name}</div>`;
                has = true;
            }
        });
        el.innerHTML = has ? html : 'Нет назначенных сотрудников';
        return;
    }

    el.innerHTML = 'Нет данных о сотрудниках';
}

//console.log("MemberName",MemberName);
//console.log("MemberId",MemberId);
//console.log("MemberPosition",MemberPosition);


// Статусы RU и карта цветов статусов из скрытого div
const STATUS_TEXTS_RU = {
    new: 'Новый',
    confirmed: 'Подтвержден',
    daily_plan: 'В плане дня',
    in_progress: 'В процессе',
    completed: 'Завершен',
    cancelled: 'Отменен',
    delayed: 'Задержан'
};

let flightStatusColorsMapCrew = null;
try {
    const colorsNodeCrew = document.getElementById('flightStatusColors');
    if (colorsNodeCrew && colorsNodeCrew.dataset && colorsNodeCrew.dataset.maps) {
        flightStatusColorsMapCrew = JSON.parse(colorsNodeCrew.dataset.maps);
    }
} catch (e) {}

function resolveCrewStatusColor(statusKey) {
    if (!flightStatusColorsMapCrew) return null;
    if (flightStatusColorsMapCrew[statusKey]) return flightStatusColorsMapCrew[statusKey];
    const ru = STATUS_TEXTS_RU[statusKey] || statusKey;
    if (flightStatusColorsMapCrew[ru]) return flightStatusColorsMapCrew[ru];
    const lowerKey = statusKey ? statusKey.toLowerCase() : '';
    if (flightStatusColorsMapCrew[lowerKey]) return flightStatusColorsMapCrew[lowerKey];
    const lowerRu = ru ? ru.toLowerCase() : '';
    if (flightStatusColorsMapCrew[lowerRu]) return flightStatusColorsMapCrew[lowerRu];
    return null;
}

const screenWidth = innerWidth-100

let canvas2=document.getElementById("canvas2")  


canvas2.width=screenWidth;
const ctx=canvas2.getContext("2d");

// ================================
// Drag-to-pan: update #dateQ by dragging canvas2 horizontally
// ================================
;(function enableCanvas2DragPan(){
    const dateInput = document.getElementById('dateQ');
    if (!canvas2 || !dateInput) return;

    let isDragging = false;
    let dragStartX = 0;
    let baseDate = null; // Date object captured at drag start

    function parseDate(iso) {
        // Expecting YYYY-MM-DD
        const [y,m,d] = (iso || '').split('-').map(Number);
        if (!y || !m || !d) return null;
        return new Date(y, m - 1, d);
    }

    function formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function onMouseDown(e){
        // Only left button
        if (e.button !== 0) return;
        isDragging = true;
        dragStartX = e.clientX;
        // Capture current date as base
        baseDate = parseDate(dateInput.value) || new Date();
        // Prevent text selection while dragging
        document.body.style.userSelect = 'none';
    }

    function onMouseMove(e){
        if (!isDragging || !baseDate) return;
        const dx = e.clientX - dragStartX; // pixels
        // Convert pixels -> minutes using current Scale (minutes per pixel)
        // Invert direction: drag right->left should INCREASE date
        const minutes = (-dx) * (typeof Scale === 'number' ? Scale : 0);
        const daysExact = minutes / (60 * 24);
        // Dragging right -> move forward in days (positive)
        const deltaDays = Math.round(daysExact);
        if (deltaDays !== 0){
            const newDate = new Date(baseDate);
            newDate.setDate(baseDate.getDate() + deltaDays);
            dateInput.value = formatDate(newDate);
        } else {
            // If < 0.5 day movement, keep base date visible
            dateInput.value = formatDate(baseDate);
        }
    }

    function endDrag(){
        if (!isDragging) return;
        isDragging = false;
        document.body.style.userSelect = '';
        // Emit change so listeners can react (e.g., redraw)
        if (dateInput) {
            const ev = new Event('change', { bubbles: true });
            dateInput.dispatchEvent(ev);
        }
    }

    canvas2.addEventListener('mousedown', onMouseDown);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', endDrag);
    canvas2.addEventListener('mouseleave', endDrag);
})();

// Добавляем обработчик mousemove для canvas2
canvas2.addEventListener('mousemove', handleMouseMove);

// Обработчик клика интегрирован в существующий ClickCanvas.addEventListener


// тултип----
// let tooltip=document.getElementById("tooltip-container")
// tooltip.style.visibility="hidden";
var canvasOffset = $("#canvas2").offset();
var offsetX = canvasOffset.left;
var offsetY = canvasOffset.top;
var tipCanvas = document.getElementById("tip");
var tipCtx = tipCanvas.getContext("2d");
// тултип

var dot;

let heightHeader=50;
let heightTableString=50;
let heightRectFlight=25;
let numberHours;

var currentDate=date1.value;
var periodNew=period.value

let long2=(canvas2.width-longFirstColumn)/periodNew

let Scale= periodNew*60*24/(canvas2.width-longFirstColumn)

var rectHover = [];
window.rectHoverCrew = rectHover; // Делаем глобально доступным для двойного клика (отдельный массив для crew canvas)



drawTable2()
window.rectHoverCrew = rectHover; // Обновляем после отрисовки


function drawTable2(){
    // Обновляем periodNew при каждом вызове drawTable2
    periodNew = period.value;
    // console.log('drawTable2: periodNew обновлен на:', periodNew);
    
    // Обновляем Scale на основе нового periodNew
    Scale = periodNew * 60 * 24 / (canvas2.width - longFirstColumn);
    // console.log('drawTable2: Scale обновлен на:', Scale);
    
let Quantity;
let TypeData;
variant=1; 

// Получаем выбранные должности
const selectedPositions = getSelectedPositionsFromStorage();
// console.log('Selected positions for filtering:', selectedPositions);

// Фильтруем сотрудников по должностям
filteredMembers = [];
filteredMemberNames = [];
filteredMemberPositions = [];
filteredMemberIds = [];

if (selectedPositions.length === 0) {
    // Если не выбрано должностей, показываем всех
    filteredMembers = MemberJson;
    filteredMemberNames = MemberName;
    filteredMemberPositions = MemberPosition;
    filteredMemberIds = MemberId;
} else {
    // Фильтруем только выбранные должности
    MemberJson.forEach((member, index) => {
        if (selectedPositions.includes(member.position)) {
            filteredMembers.push(member);
            filteredMemberNames.push(MemberName[index]);
            filteredMemberPositions.push(MemberPosition[index]);
            filteredMemberIds.push(MemberId[index]);
        }
    });
}

// Экспортируем filteredMembers в глобальную область видимости для использования в RectMovement.js
window.filteredMembers = filteredMembers;
window.filteredMemberIds = filteredMemberIds;

Quantity = filteredMembers.length; 

// console.log('Filtered members count:', Quantity, 'of', MemberJson.length);

    show_flight = true;
    show_measures = true;
    show_maintenance = false;


canvas2.height=(Quantity+1)*50+16;

//console.log(variant);

    let lineNumber=Quantity+1;
    let xmax=canvas.width
    let ymax=canvas.height

//рисуем шапку
    ctx.fillStyle="#1E64D4"
    ctx.fillRect(1, 1, xmax, heightHeader-1);

// пишем ФИО и должность

    for(let i=0; i<Quantity; i++) {
    ctx.fillStyle ="black";
    ctx.font = "17px Arial";
    ctx.fillText(filteredMemberNames[i], 10, heightHeader+heightTableString*(i+1)-20)

    ctx.font = "14px Arial";
    ctx.fillStyle ="gray";
  
    {ctx.fillText(filteredMemberPositions[i], 10, heightHeader+heightTableString*(i+1)-3)

    // Рисуем круг статуса для ЧЛЭ
    ctx.beginPath();
    let statusColor = getCrewStatusColor(filteredMemberIds[i]);
    ctx.fillStyle = statusColor;
    ctx.arc(200, heightHeader+heightTableString*(i+1)-20, 10, 0, 2 * Math.PI);
    ctx.fill();

}

    }

//рисуем горизонтальные линии

    for(let i=0; i<=lineNumber; i++) {

        ctx.beginPath();
        ctx.moveTo(0.5, (i*heightTableString)+0.5);
        ctx.lineTo(xmax+0.5,(i*heightTableString)+0.5)
        ctx.lineWidth = 0.5;
        ctx.strokeStyle ="gray";
        ctx.closePath()
        ctx.stroke();

    }



//рисуем подписи дней и вертикальные линии
    ctx.fillStyle="white"
    ctx.strokeStyle ="gray";
    let long=(xmax-longFirstColumn)/periodNew

    for(let i1=0;i1<periodNew;i1++){

        currentDate=new Date(document.getElementById('dateQ').value)
        currentDate.setDate(currentDate.getDate() + i1);
        //console.log(currentDate);


        // Для периода "Месяц" показываем только день, для остальных - день и месяц
        let newDate1;
        if (parseInt(periodNew) === 30) {
            newDate1 = currentDate.toLocaleDateString("ru-RU", {day: "numeric"});
        } else {
            newDate1 = currentDate.toLocaleDateString("ru-RU", {day: "numeric", month: "short" });
        }

        ctx.font = "18px Arial";
        var longDate= ctx.measureText(newDate1);


        ctx.fillText(newDate1, longFirstColumn+(i1*(long))+(long/2)-(longDate.width/2)+0.5, 35.5)

        ctx.beginPath();
        ctx.moveTo(longFirstColumn+long*i1+0.5, 0.5);
        ctx.lineTo(longFirstColumn+long*i1+0.5, lineNumber*50+0.5);
        ctx.lineWidth = 0.5;
        ctx.strokeStyle="gray";
        ctx.stroke();


        if(periodNew==7 || periodNew==30) numberHours=4; else numberHours=24;
        for(let j=0;j<=numberHours;j++){

           
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(longFirstColumn+long*i1+j*(long/numberHours)+0.5, heightHeader-5);
            ctx.lineTo(longFirstColumn+long*i1+j*(long/numberHours)+0.5, heightHeader);
            ctx.strokeStyle="white";
            ctx.stroke();




        }


    }


ctx.lineWidth = 0.5;
ctx.beginPath();



// первая вертикальная линия в таблице

    ctx.moveTo(0.5, 0.5);
    ctx.lineTo(0.5, heightHeader+Quantity*50+0.5);

// последняя  вертикальная линия в таблице

    ctx.moveTo(xmax-0.5, 0.5);
    ctx.lineTo(xmax-0.5, heightHeader+Quantity*50+0.5);


// линия текущего времени

     var CurrentTime=document.getElementById("CurrentTime").value;

    ctx.strokeStyle="black";
    ctx.stroke();

    // Проверяем, попадает ли текущее время в диапазон отображаемых дат
    const currentTimeX = longFirstColumn + CurrentTime/Scale;
    const canvasEndX = canvas2.width;
    
    // Рисуем линию только если текущее время попадает в диапазон
    if (currentTimeX >= longFirstColumn && currentTimeX <= canvasEndX) {
        ctx.beginPath();
        ctx.moveTo(currentTimeX, heightHeader+0.5);
        ctx.lineTo(currentTimeX, heightHeader+Quantity*50+0.5);
        ctx.strokeStyle="red";
        ctx.lineWidth=2;
        ctx.stroke();

        // Отрисовываем серый оверлей прошедшего времени         drawPastTimeOverlay();

        // рисуем треугольник на времени
        ctx.beginPath();
        ctx.moveTo(currentTimeX-5, 50);
        ctx.lineTo(currentTimeX+5, 50);
        ctx.lineTo(currentTimeX, 55);
        ctx.fillStyle="red";
        ctx.fill();
    }




    rectHover.length=0;
    // рисуем прямоугольники


    rectHover.push({
        x: 0,
        y: 0,
        XLong: 0,
        heightRectFlight: 0,
        });

    let totalElements = 0; // Счетчик для всех элементов

    for (var key in data) {

    // console.log(data, "data");
    
    // Skip if data[key] is undefined or doesn't have required properties
    if (!data[key] || !data[key].crew) {
        console.warn('Skipping flight with missing data:', key);
        continue;
    }

        let startFL = data[key].start
        if(startFL<0) startFL=0

        let finishFL = data[key].finish
        let aircraft = data[key].aircraft
        let id = data[key].id
        let activity_type = data[key].activity_type
        let flight_number = data[key].flight_number
        let arrival_airport = data[key].arrival_airport
        let departure_airport = data[key].departure_airport
        let time_start = data[key].time_start
        let time_finish = data[key].time_finish
        
        // Динамически извлекаем все должности из crew объекта
        let crewMembers = {};
        if (data[key].crew && typeof data[key].crew === 'object') {
            // Проходим по всем должностям в crew
            for (let position in data[key].crew) {
                if (data[key].crew.hasOwnProperty(position) && data[key].crew[position]) {
                    crewMembers[position] = data[key].crew[position].id || null;
                }
            }
        }
        
        let status= data[key].status
        let trip_number= data[key].trip_number
        let passengers_count= data[key].passengers_count
        let checklist_completed= data[key].checklist_completed

        if ((activity_type === 'FL')) 
        {
            console.log(activity_type, "activity_type");
            rect(startFL, finishFL, aircraft, id, flight_number, arrival_airport, departure_airport, time_start, time_finish, status, trip_number, passengers_count, checklist_completed, activity_type, crewMembers)
          
        }

        if(activity_type === 'crew_events'){

           // alert("зашел в rectEvents");
            let event_id = data[id]?.event_id || 1; // Получаем event_id из данных мероприятия
                console.log('Отображение мероприятия:', {
                flight_id: id,
                event_id: event_id,
                event_data: data[id]
                
            });
           /* alert("зашел в rectEvents"); */
            rectEvents(startFL, finishFL, id, event_id)
        }
        
        if(activity_type === 'maintenance'){

           // rectMaintenance(startFL, finishFL, id, maintenance)
        }


    
    




    }



}




function cleanCanvas()
{
    ctx.clearRect(0, 0, canvas2.width, canvas2.height);

}



function rect(Xstart, Xfinish, aircraft, id, flight_number, arrival_airport, departure_airport, time_start, time_finish, status, trip_number, passengers_count, checklist_completed, activity_type, crewMembers) {
    let y;

    let Element=filteredMemberIds; // Используем отфильтрованные ID

    // Используем переданный объект crewMembers для динамической обработки всех должностей
    let allCrewPositions = crewMembers || {};

    // Находим текущий полет в data (выносим за цикл для оптимизации)
    let currentFlight = Object.values(data).find(flight => 
            flight.id === id
    );

    // Динамически обрабатываем все должности из crewMembers
    for (let position in allCrewPositions) {
        if (allCrewPositions.hasOwnProperty(position) && allCrewPositions[position]) {
            y = 0;
            let flight_active = false;
            
            let crewId = allCrewPositions[position];
            let index = Element.findIndex(e => e == crewId);
            if (index >= 0) {
                y = index * 50 + 55;
                if (y >= 55) flight_active = true;
            }

            if(flight_active===true) {
                // Цвет из справочника статусов, переданный с бэка (приоритетно)
                let statusColor = (currentFlight && (currentFlight.color || currentFlight.event_color)) || null;
                // Если цвет не передан, используем локальные фолбэки
                if (!statusColor) {
                    switch(currentFlight?.status) {
                        case 'new':
                            statusColor = '#E8F5E9'; // зелёный
                            break;
                        case 'confirmed':
                            statusColor = '#C8E6C9';  // светло-зелёный
                            break;
                        case 'daily_plan':
                            statusColor = '#E3F2FD'; // синий 
                            break;
                        case 'in_progress':
                            statusColor = '#FFF3E0'; // оранжевый
                            break;
                        case 'completed':
                            statusColor = '#E8F5E9'; // зелёный
                            break;
                        case 'cancelled':
                            statusColor = '#FFEBEE'; // красный
                            break;
                        case 'delayed':
                            statusColor = '#FFF8E1'; // жёлтый
                            break;
                        default:
                            statusColor = '#F5F5F5'; // серый
                    }
                }

                if(activity_type === 'maintenance') {
                    statusColor = 'rgb(243, 11, 11)'; // Оранжевый по умолчанию
                }

                if(activity_type === 'crew_events') {
                   const evt = Object.values(data).find(f => f && f.id === id);
                   const evtColor = (evt && (evt.event_color || evt.color || (evt.event && evt.event.color))) || 'rgba(58, 118, 248, 0.78)';
                   statusColor = evtColor;
                }

                // Рисуем основной прямоугольник с цветом статуса
                ctx.fillStyle = statusColor;
                const XStartPrived = longFirstColumn + (Xstart/Scale);
                const XLong = (Xfinish - Xstart)/Scale;
                ctx.fillRect(XStartPrived, y, XLong, heightRectFlight);

                // Рисуем фактический прямоугольник (если есть данные)
                if(currentFlight && currentFlight.actual_time_departure && currentFlight.actual_time_arrival) {
                    // Используем планируемые даты для фактического времени
                    let actualStart = new Date(currentFlight.date_departure + ' ' + currentFlight.actual_time_departure);
                    let actualFinish = new Date(currentFlight.date_arrival + ' ' + currentFlight.actual_time_arrival);
                    
                    // Вычисляем позицию относительно начала периода
                    let actualXStart = (actualStart - new Date(date1.value)) / (1000 * 60);
                    let actualXFinish = (actualFinish - new Date(date1.value)) / (1000 * 60);
                   
                    if(activity_type === 'flight') {
                        // Рисуем оранжевый прямоугольник для фактического времени
                        ctx.fillStyle = "rgb(255,159,10)";
                        let actualXStartPrived = longFirstColumn + (actualXStart/Scale);
                        let actualXLong = (actualXFinish - actualXStart)/Scale;
                        ctx.fillRect(actualXStartPrived, y + heightRectFlight + 5, actualXLong, 5);
                    }
                }

                rectHover.push({
                    id: id,
                    x: XStartPrived ? XStartPrived : 0,
                    y: y ? y : 0,
                    XLong: XLong ? XLong : 0,
                    heightRectFlight: heightRectFlight ? heightRectFlight : 0,
                    activity_type: activity_type,
                    aircraft: aircraft ? aircraft : null,
                    flight_number: flight_number ? flight_number : null,
                    arrival_airport: arrival_airport ? arrival_airport : null,
                    departure_airport: departure_airport,
                    time_start: time_start,
                    time_finish: time_finish,
                    actual_time_departure: currentFlight ? currentFlight.actual_time_departure : null,
                    actual_time_arrival: currentFlight ? currentFlight.actual_time_arrival : null,
                    status: status ? status : null,
                    crew: allCrewPositions // Сохраняем все должности
                });

                ctx.fillStyle = "#333";
                ctx.font = "12px Arial";

                // рисуем номер рейса
                ctx.fillText(flight_number, XStartPrived, y+heightRectFlight/4);

                // рисуем аэропорт вылета
                if (Xstart>20) ctx.fillText(departure_airport, XStartPrived-35, y+heightRectFlight/2);

                // рисуем аэропорт прилета
                ctx.fillText(arrival_airport, XStartPrived+XLong+5, y+heightRectFlight/2);

                const time_startHM = time_start.substr(0, 5);
                const time_finishHM = time_finish.substr(0, 5);

                // рисуем время вылета
                if (Xstart>20) ctx.fillText(time_startHM, XStartPrived-35, y+heightRectFlight);

                // рисуем время прилета
                ctx.fillText(time_finishHM, XStartPrived+XLong+5, y+heightRectFlight);
            } // конец по условию flight_active
        } // конец проверки должности
    } // конец цикла по всем должностям из crewMembers
}





function rectEvents(Xstart, Xfinish, flight_id, event_id) {
 
    // Получаем данные о мероприятии
    let currentEvent = Object.values(data).find(event => 
        event.id === flight_id
    );

    if (!currentEvent) {
        // console.log('Мероприятие не найдено:', flight_id);
        return;
    }

    // Получаем данные о назначенных сотрудниках из events_crew
    let assignedCrew = [];
    if (currentEvent.crew && Array.isArray(currentEvent.crew) && currentEvent.crew.length > 0) {
        assignedCrew = currentEvent.crew;
    }

    // Если нет назначенных сотрудников, не отображаем мероприятие
    if (assignedCrew.length === 0) {
        // console.log('У мероприятия нет назначенных сотрудников:', flight_id);
        return;
    }

    // Отображаем мероприятие для каждого назначенного сотрудника
    assignedCrew.forEach(crewMember => {
        if (crewMember && crewMember.id) {
            let y = 0;
            let Element = filteredMemberIds; // Используем отфильтрованные ID
            
            // Находим позицию сотрудника в списке
            let index = Element.findIndex(e => e == crewMember.id);
            if (index >= 0) {
                y = index * 50 + 55;
                
                // Цвет для мероприятий — берем из данных события, если есть
                let statusColor = (currentEvent && (currentEvent.event_color || currentEvent.color || (currentEvent.event && currentEvent.event.color)))
                    ? (currentEvent.event_color || currentEvent.color || (currentEvent.event && currentEvent.event.color))
                    : 'rgba(58, 118, 248, 0.78)';
                
                // Рисуем прямоугольник мероприятия
                ctx.fillStyle = statusColor;
                const XStartPrived = longFirstColumn + (Xstart/Scale);
                const XLong = (Xfinish - Xstart)/Scale;
                ctx.fillRect(XStartPrived, y, XLong, heightRectFlight);

                // Добавляем в hover массив для обработки событий
                rectHover.push({
                    id: flight_id,
                    x: XStartPrived,
                    y: y,
                    XLong: XLong,
                    heightRectFlight: heightRectFlight,
                    activity_type: 'crew_events',
                    event_id: event_id,
                    crew_id: crewMember.id,
                    crew_name: crewMember.name,
                    crew_position: crewMember.position,
                    time_start: currentEvent.time_start,
                    time_finish: currentEvent.time_finish,
                    flight_type: currentEvent.flight_type,
                    event_type_name: currentEvent.event_type_name
                });
                
                console.log('Добавлено мероприятие в rectHover:', {
                    id: flight_id,
                    x: XStartPrived,
                    y: y,
                    XLong: XLong,
                    activity_type: 'crew_events',
                    crew_name: crewMember.name
                });

                // Рисуем текст
                ctx.fillStyle = "#333";
                ctx.font = "12px Arial";

                // Рисуем тип мероприятия
                if (currentEvent.event_type_name) {
                    console.log('Отображение типа мероприятия:', currentEvent.event_type_name);
                    ctx.fillText(currentEvent.event_type_name, XStartPrived, y + heightRectFlight/1.5);
                } else if (currentEvent.flight_type) {
                    console.log('Отображение flight_type:', currentEvent.flight_type);
                    ctx.fillText(currentEvent.flight_type, XStartPrived, y + heightRectFlight/1.5);
                } else {
                    console.log('Отображение стандартного названия: Мероприятие');
                    ctx.fillText('Мероприятие', XStartPrived, y + heightRectFlight/1.5);
                }

                // Рисуем время начала
                if (Xstart > 20 && currentEvent.time_start) {
                    const time_startHM = currentEvent.time_start.substr(0, 5);
                    ctx.fillText(time_startHM, XStartPrived - 35, y + heightRectFlight);
                }

                // Рисуем время окончания
                if (currentEvent.time_finish) {
                    const time_finishHM = currentEvent.time_finish.substr(0, 5);
                    ctx.fillText(time_finishHM, XStartPrived + XLong + 5, y + heightRectFlight);
                }
            }
        }
    });
}























$("#canvas2").mousemove(function(e){handleMouseMove(e);});

// Функция для отображения tooltip'а со статусами
function showStatusTooltip(e, crewStatus) {
    // Заполняем статусный тултип реальными данными
    let requirementsText = "";
    let expiredCount = 0;
    let warningCount = 0;
    let expiringCount = 0;
    let activeCount = 0;
    
    // Проверяем, есть ли типы ВС у сотрудника
    if (crewStatus.aircraft_types && crewStatus.aircraft_types.length > 0) {
        // Создаем таблицу с колонками для каждого типа ВС
        requirementsText = `<div style="display: flex; gap: 15px; flex-wrap: wrap;">`;
        
        crewStatus.aircraft_types.forEach(aircraftType => {
            let typeExpiredCount = 0;
            let typeWarningCount = 0;
            let typeExpiringCount = 0;
            let typeActiveCount = 0;
            
            let typeRequirementsText = "";
            
            aircraftType.requirements.forEach(req => {
                let statusText = "";
                let statusColor = "";
                
                switch(req.status) {
                    case 'expired':
                        statusText = "Истек";
                        statusColor = "#FF3B30";
                        typeExpiredCount++;
                        expiredCount++;
                        break;
                    case 'warning':
                        statusText = "Предупреждение";
                        statusColor = "#FFCC00";
                        typeWarningCount++;
                        warningCount++;
                        break;
                    case 'expiring':
                        statusText = "Истекает";
                        statusColor = "#FF9500";
                        typeExpiringCount++;
                        expiringCount++;
                        break;
                    case 'active':
                        statusText = "Активен";
                        statusColor = "#34C759";
                        typeActiveCount++;
                        activeCount++;
                        break;
                    default:
                        statusText = "Нет данных";
                        statusColor = "#C7C7CC";
                }
                
                typeRequirementsText += `<div style="margin: 3px 0; padding: 3px; border-radius: 3px; background: ${statusColor}20; font-size: 11px;">
                    <strong>${req.name}</strong><br>
                    <span style="color: ${statusColor}; font-weight: bold;">${statusText}</span>
                    ${req.expiry_date !== '-' ? `<br><small>Срок: ${req.expiry_date}</small>` : ''}
                </div>`;
            });
            
            // Создаем колонку для типа ВС
            requirementsText += `
                <div style="flex: 1; min-width: 200px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 8px; background: #f9f9f9;">
                    <div style="font-weight: bold; text-align: center; margin-bottom: 8px; color: #333; background: #007bff; color: white; padding: 4px; border-radius: 4px;">
                        ${aircraftType.aircraft_type_icao} (${aircraftType.aircraft_type_name})
                    </div>
                    <div style="font-size: 10px; margin-bottom: 6px; text-align: center;">
                        <span style="color: #FF3B30;">Истек: ${typeExpiredCount}</span> | 
                        <span style="color: #FFCC00;">Предупреждение: ${typeWarningCount}</span> | 
                        <span style="color: #FF9500;">Истекает: ${typeExpiringCount}</span> | 
                        <span style="color: #34C759;">Активен: ${typeActiveCount}</span>
                    </div>
                    <div style="max-height: 200px; overflow-y: auto;">
                        ${typeRequirementsText}
                    </div>
                </div>
            `;
        });
        
        requirementsText += `</div>`;
    } else {
        // Fallback для старого формата (если нет aircraft_types)
        crewStatus.requirements?.forEach(req => {
                        let statusText = "";
                        let statusColor = "";
                        
                        switch(req.status) {
                            case 'expired':
                                statusText = "Истек";
                                statusColor = "#FF3B30";
                                expiredCount++;
                                break;
                            case 'warning':
                                statusText = "Предупреждение";
                                statusColor = "#FFCC00";
                                warningCount++;
                                break;
                            case 'expiring':
                                statusText = "Истекает";
                                statusColor = "#FF9500";
                                expiringCount++;
                                break;
                            case 'active':
                                statusText = "Активен";
                                statusColor = "#34C759";
                                activeCount++;
                                break;
                            default:
                                statusText = "Нет данных";
                                statusColor = "#C7C7CC";
                        }
                        
                        requirementsText += `<div style="margin: 4px 0; padding: 4px; border-radius: 4px; background: ${statusColor}20;">
                            <strong>${req.name}</strong><br>
                            <span style="color: ${statusColor}; font-weight: bold;">${statusText}</span>
                            ${req.expiry_date !== '-' ? `<br><small>Срок: ${req.expiry_date}</small>` : ''}
                        </div>`;
                    });
    }
                    
                    // Обновляем содержимое тултипа
                    const status3vp = document.getElementById("status-3vp");
                    const status3vpn = document.getElementById("status-3vpn");
                    const statusLimits = document.getElementById("status-limits");
                    
                    if (status3vp) {
                        status3vp.innerHTML = `
                            <div style="font-weight: bold; margin-bottom: 8px;">Статус: ${getStatusText(crewStatus.overall_status)}</div>
                            <div style="font-size: 12px; margin-bottom: 8px;">
                                <span style="color: #FF3B30;">Истек: ${expiredCount}</span> | 
                                <span style="color: #FFCC00;">Предупреждение: ${warningCount}</span> | 
                                <span style="color: #FF9500;">Истекает: ${expiringCount}</span> | 
                                <span style="color: #34C759;">Активен: ${activeCount}</span>
                            </div>
                        `;
                    }
                    
                    if (status3vpn) {
                        status3vpn.innerHTML = requirementsText;
                    }
                    
                    if (statusLimits) {
                        statusLimits.innerText = `Сотрудник: ${crewStatus.name} (${crewStatus.position})`;
                    }
                
                // Показываем статусный тултип
                let statusTooltip = document.getElementById("status-tooltip");
                if (!statusTooltip) {
                    console.error('Status tooltip element not found');
                    return;
                }
                
    // Позиционируем и зажимаем во вьюпорте
    statusTooltip.style.visibility = "hidden";
    statusTooltip.style.display = "block";
    statusTooltip.style.left = "0px";
    statusTooltip.style.top = "0px";

    const rect = statusTooltip.getBoundingClientRect();
    const margin = 10;
    const maxLeft = window.innerWidth - rect.width - margin;
    const maxTop = window.innerHeight - rect.height - margin;

    const left = Math.max(margin, Math.min(e.clientX + 20, maxLeft));
    const top = Math.max(margin, Math.min(e.clientY + 20, maxTop));

    statusTooltip.style.left = left + "px";
    statusTooltip.style.top = top + "px";
                statusTooltip.style.visibility = "visible";
}

// Логика клика по кругам статуса интегрирована в существующий обработчик ClickCanvas

// show tooltip when mouse hovers over dot
function handleMouseMove(e){
    const canvas2 = document.getElementById('canvas2');
    if (!canvas2) return; // Добавляем проверку на существование canvas

    // Tooltip используем тот же, что и для верхнего канваса
    const aircraftStatusTooltip = document.getElementById("aircraft-status-tooltip");
    if (aircraftStatusTooltip) {
        // Скрываем по умолчанию; покажем, только если найдём попадание
        aircraftStatusTooltip.style.visibility = "hidden";
    }

    let Quantity;
    let TypeData;
    
    variant = 1; 
    Quantity = MemberQuantity; 
    TypeData = MemberName;

    // Получаем позицию курсора относительно canvas
    const rect = canvas2.getBoundingClientRect(); 
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;

    let hit = false;
    
    // tooltip статусов управляется только кликами
    hoveredObj = "false";
   
    // Проверяем наведение на прямоугольники рейсов и мероприятий
    if (!hit) {
        for (let i = 0; i < rectHover.length; i++) {
            dot = rectHover[i];
            // Skip if dot is undefined
            if (!dot) {
                continue;
            }
            
            const dx = mouseX;
            const dy = mouseY;

            if ((dx > dot.x) && (dx < (dot.x + dot.XLong)) && (dy > dot.y) && (dy < (dot.y + heightRectFlight))) {
                console.log('Попадание в прямоугольник! Элемент:', i, 'Тип:', dot.activity_type);
                
                // Общие поля для всех типов
                id = dot.id;
                activity_type = dot.activity_type;
                time_start = dot.time_start;
                time_finish = dot.time_finish;
                
                // Если это мероприятие (crew_events)
                if (dot.activity_type === 'crew_events') {
                    // Поля для мероприятий
                    event_id = dot.event_id;
                    crew_id = dot.crew_id;
                    crew_name = dot.crew_name;
                    crew_position = dot.crew_position;
                    flight_type = dot.flight_type;
                    event_type_name = dot.event_type_name;
                    
                    // Устанавливаем значения по умолчанию для полей рейсов
                    aircraft = '-';
                    flight_number = '-';
                    arrival_airport = '-';
                    departure_airport = '-';
                    actual_time_departure = '-';
                    actual_time_arrival = '-';
                    status = 'Мероприятие';
                    trip_number = '-';
                    passengers_count = '-';
                    checklist_completed = '-';
                } else {
                    // Поля для рейсов
                    aircraft = dot.aircraft;
                    flight_number = dot.flight_number;
                    arrival_airport = dot.arrival_airport;
                    departure_airport = dot.departure_airport;
                    actual_time_departure = dot.actual_time_departure;
                    actual_time_arrival = dot.actual_time_arrival;    
                    status = dot.status;
                    trip_number = dot.trip_number;
                    passengers_count = dot.passengers_count;
                    checklist_completed = dot.checklist_completed;
                }
                
                x1stroke = dot.x;
                y1stroke = dot.y;
                XLongStroke = dot.XLong;
                heightRectFlight1stroke = heightRectFlight;
             
                hit = true;
                hoveredObj = "true";
                console.log('hoveredObj установлен в handleMouseMove:', hoveredObj);

                // --- Показ тултипа по наведению ---
                if (aircraftStatusTooltip) {
                    let html = '';

                    if (dot.activity_type === 'crew_events') {
                        // Получаем комментарий/примечания и даты из основного массива data
                        let notesText = '-';
                        let eventRow = null;
                        try {
                            if (id != null) {
                                const lookup = String(id);
                                eventRow = Object.values(data).find(f => f && f.activity_type === 'crew_events' && String(f.id) === lookup);
                                if (eventRow) {
                                    if (eventRow.notes != null && String(eventRow.notes).trim() !== '') {
                                        notesText = eventRow.notes;
                                    } else {
                                        const candidates = ['comment','comments','note','remark','remarks','description','descr'];
                                        for (const key of candidates) {
                                            if (eventRow[key] != null && String(eventRow[key]).trim() !== '') {
                                                notesText = eventRow[key];
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (err) {
                            console.error('Ошибка при получении комментария мероприятия:', err);
                        }

                        const timeStartHM = time_start ? time_start.substr(0, 5) : '-';
                        const timeFinishHM = time_finish ? time_finish.substr(0, 5) : '-';
                        const dateStartRaw = eventRow && (eventRow.date_departure || eventRow.date_start) ? (eventRow.date_departure || eventRow.date_start) : '';
                        const dateEndRaw = eventRow && (eventRow.date_arrival || eventRow.date_end) ? (eventRow.date_arrival || eventRow.date_end) : '';

                        // Преобразуем YYYY-MM-DD в ДД.ММ.ГГГГ
                        const formatRuDate = (dateStr) => {
                            if (!dateStr || typeof dateStr !== 'string') return '';
                            const parts = dateStr.split('-');
                            if (parts.length !== 3) return dateStr;
                            const [y, m, d] = parts;
                            return `${d.padStart(2, '0')}.${m.padStart(2, '0')}.${y}`;
                        };

                        const dateStart = dateStartRaw ? formatRuDate(dateStartRaw) : '';
                        const dateEnd = dateEndRaw ? formatRuDate(dateEndRaw) : '';
                        const startDateTime = dateStart ? `${dateStart} ${timeStartHM}` : timeStartHM;
                        const endDateTime = dateEnd ? `${dateEnd} ${timeFinishHM}` : timeFinishHM;
                        const eventTitle = event_type_name || flight_type || 'Мероприятие';
                        const crewText = crew_name
                            ? (crew_position ? `${crew_name} (${crew_position})` : crew_name)
                            : '-';

                        html = `
                            <div>Мероприятие: ${eventTitle}</div>
                            <div>Сотрудник: ${crewText}</div>
                            <div>Дата/время начала: ${startDateTime}</div>
                            <div>Дата/время окончания: ${endDateTime}</div>
                            <div>Комментарий: ${notesText}</div>
                        `;
                    } else {
                        const timeStartHM = time_start ? time_start.substr(0, 5) : '-';
                        const timeFinishHM = time_finish ? time_finish.substr(0, 5) : '-';

                        html = `
                            <div>Рейс: ${flight_number || '-'}</div>
                            <div>ВС: ${aircraft || '-'}</div>
                            <div>Вылет: ${departure_airport || '-'}</div>
                            <div>Прилет: ${arrival_airport || '-'}</div>
                            <div>Время вылета: ${timeStartHM}</div>
                            <div>Время прилета: ${timeFinishHM}</div>
                            <div>Статус: ${status || '-'}</div>
                        `;
                    }

                    aircraftStatusTooltip.innerHTML = html;

                    // Позиционирование тултипа рядом с курсором, не выходя за границы окна
                    aircraftStatusTooltip.style.visibility = 'hidden';
                    aircraftStatusTooltip.style.display = 'block';
                    aircraftStatusTooltip.style.left = '0px';
                    aircraftStatusTooltip.style.top = '0px';

                    const tRect = aircraftStatusTooltip.getBoundingClientRect();
                    const margin = 10;
                    const maxLeft = window.innerWidth - tRect.width - margin;
                    const maxTop = window.innerHeight - tRect.height - margin;
                    const left = Math.max(margin, Math.min(e.clientX + 20, maxLeft));
                    const top = Math.max(margin, Math.min(e.clientY + 20, maxTop));
                    aircraftStatusTooltip.style.left = left + 'px';
                    aircraftStatusTooltip.style.top = top + 'px';
                    aircraftStatusTooltip.style.visibility = 'visible';
                }

                // Дальше искать не нужно — уже нашли нужный прямоугольник
                break;
            }
        }
    }

    // Если ни в один прямоугольник не попали — скрываем тултип
    if (!hit && aircraftStatusTooltip) {
        aircraftStatusTooltip.style.visibility = "hidden";
    }
}





// ЕДИНЫЙ обработчик клика с четким приоритетом: сначала круги статуса, потом прямоугольники
ClickCanvas.addEventListener('click', (event) => {
   clearHighlight();
   
   const canvas2 = document.getElementById('canvas2');
   if (!canvas2) return;
   
   const rect = canvas2.getBoundingClientRect();
   const mouseX = event.clientX - rect.left;
   const mouseY = event.clientY - rect.top;

   // Скрываем все tooltip'ы перед показом новых
   const flightTooltip = document.getElementById('flight-tooltip');
   const eventTooltip = document.getElementById('event-tooltip');
   const statusTooltip = document.getElementById('status-tooltip');
   
   if (flightTooltip) {
       flightTooltip.style.visibility = 'hidden';
       flightTooltip.style.display = 'none';
   }
   if (eventTooltip) {
       eventTooltip.style.visibility = 'hidden';
       eventTooltip.style.display = 'none';
   }
   if (statusTooltip) {
       statusTooltip.style.visibility = 'hidden';
   }

   // ПРИОРИТЕТ 1: Проверяем клик по кругам статуса ПЕРВЫМ
   const selectedPositions = getSelectedPositionsFromStorage();
   let filteredMembers = [];
   
   if (selectedPositions.length === 0) {
       filteredMembers = MemberJson;
   } else {
       filteredMembers = MemberJson.filter(member => selectedPositions.includes(member.position));
   }

   const Quantity = filteredMembers.length;

   // Проверяем клик по кругам статуса
   for(let i = 0; i < Quantity; i++) {
       // Координаты круга
       let circleX = 200;
       let circleY = heightHeader + heightTableString * (i + 1) - 20;
       let circleRadius = 15; // Увеличиваем радиус для более надежного определения
       
       // Проверяем попадание в круг
       let dx = mouseX - circleX;
       let dy = mouseY - circleY;
       let distance = Math.sqrt(dx * dx + dy * dy);
       
       if(distance <= circleRadius) {
           // Получаем ID сотрудника из отфильтрованного списка
           const crewId = filteredMemberIds[i];
           const crewStatus = crewStatusMap[crewId];
           
           if (crewStatus) {
               console.log('Клик по кругу статуса для сотрудника:', crewId, 'Distance:', distance, 'Radius:', circleRadius);
               
               // Сбрасываем hoveredObj, чтобы не обрабатывался клик по прямоугольнику
               hoveredObj = "false";
               
               // Показываем статусный тултип
               showStatusTooltip(event, crewStatus);
               
               // ВАЖНО: Выходим из обработчика СРАЗУ, не проверяя прямоугольники
               console.log('Клик по кругу статуса обработан, выходим из обработчика');
               return;
           }
       }
   }
   
   // ПРИОРИТЕТ 2: Если клик НЕ попал в круг статуса, проверяем прямоугольники рейсов

       // Проверяем клик по прямоугольникам рейсов и мероприятий
       // ВАЖНО: hoveredObj был сброшен в начале обработчика, поэтому нужно проверить попадание заново
       // Проверяем попадание в прямоугольники рейсов/мероприятий
       const canvas2ForRect = document.getElementById('canvas2');
       if (canvas2ForRect) {
           const rectForRect = canvas2ForRect.getBoundingClientRect();
           const mouseXForRect = event.clientX - rectForRect.left;
           const mouseYForRect = event.clientY - rectForRect.top;
           
           let clickedOnRect = false;
           for (var i = 0; i < rectHover.length; i++) {
               dot = rectHover[i];
               if (!dot) continue;
               
               if((mouseXForRect > dot.x) && (mouseXForRect < (dot.x + dot.XLong)) && 
                  (mouseYForRect > dot.y) && (mouseYForRect < (dot.y + heightRectFlight))) {
                   // Обновляем переменные для highlight
                   id = dot.id;
                   activity_type = dot.activity_type;
                   time_start = dot.time_start;
                   time_finish = dot.time_finish;
                   
                   if (dot.activity_type === 'crew_events') {
                       event_id = dot.event_id;
                       crew_id = dot.crew_id;
                       crew_name = dot.crew_name;
                       crew_position = dot.crew_position;
                       flight_type = dot.flight_type;
                       event_type_name = dot.event_type_name;
                       aircraft = '-';
                       flight_number = '-';
                       arrival_airport = '-';
                       departure_airport = '-';
                       actual_time_departure = '-';
                       actual_time_arrival = '-';
                       status = 'Мероприятие';
                       trip_number = '-';
                       passengers_count = '-';
                       checklist_completed = '-';
                   } else {
                       aircraft = dot.aircraft;
                       flight_number = dot.flight_number;
                       arrival_airport = dot.arrival_airport;
                       departure_airport = dot.departure_airport;
                       actual_time_departure = dot.actual_time_departure;
                       actual_time_arrival = dot.actual_time_arrival;
                       status = dot.status;
                       trip_number = dot.trip_number;
                       passengers_count = dot.passengers_count;
                       checklist_completed = dot.checklist_completed;
                   }
                   
                   x1stroke = dot.x;
                   y1stroke = dot.y;
                   XLongStroke = dot.XLong;
                   heightRectFlight1stroke = heightRectFlight;
                   clickedOnRect = true;
                   break;
               }
           }
           
           if (!clickedOnRect) {
               // Если клик не попал ни в круг статуса, ни в прямоугольник, выходим
               return;
           }
       }

    // Tooltip для рейсов (FL) удален - используется модальное окно при двойном клике
    // Tooltip для мероприятий (crew_events) оставлен
    
    // Теперь обрабатываем клик по прямоугольнику (клик точно попал в прямоугольник)
    {
        // Обновляем координаты для highlight (красная обводка)
        // Эти переменные уже установлены в handleMouseMove, но убедимся что они актуальны
        console.log('Highlight coordinates:', {
            x: x1stroke,
            y: y1stroke,
            width: XLongStroke,
            height: heightRectFlight1stroke
        });
     
  // Показываем основной тултип
                let tooltip = document.getElementById("tooltip-container");
                let eventTooltip = document.getElementById("event-tooltip-container");
                
                // Сначала скрываем только eventTooltip, tooltip будем показывать
                if (eventTooltip) { eventTooltip.style.visibility = "hidden"; eventTooltip.style.display = "none"; }
                
                // Координаты отображения - позиционируем в центре экрана для отладки
                const leftPos = "50px";
                const topPos = "50px";
                
                console.log('Координаты tooltip:', {
                    canvasWidth: canvas2.offsetWidth,
                    leftPos,
                    topPos,
                    screenWidth: window.innerWidth,
                    screenHeight: window.innerHeight
                });
                
                if (activity_type === 'crew_events') {
                    // Получаем или создаем tooltip для мероприятий
                    let newEventTooltip = document.getElementById('event-tooltip');
                    if (!newEventTooltip) {
                        newEventTooltip = document.createElement('div');
                        newEventTooltip.id = 'event-tooltip';
                        document.body.appendChild(newEventTooltip);
                    }
                    
                    // Получаем примечания
                    let notes = '-';
                    if (id) {
                        const lookup = String(id);
                        const eventRow = Object.values(data).find(f => f && f.activity_type === 'crew_events' && String(f.id) === lookup);
                        if (eventRow) {
                            if (eventRow.notes != null && String(eventRow.notes).trim() !== '') { 
                                notes = eventRow.notes; 
                                } else {
                                    const candidates = ['comment','comments','note','remark','remarks','description','descr'];
                                    for (const key of candidates) {
                                    if (eventRow[key] != null && String(eventRow[key]).trim() !== '') { 
                                        notes = eventRow[key]; 
                                        break; 
                                    }
                                }
                            }
                        }
                    }
                    
                    newEventTooltip.innerHTML = `
                        <div style="color: black; font-size: 13px; width: 100%;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tbody>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">ID</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${id || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Тип</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${activity_type || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Сотрудник</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${crew_name ? `${crew_name} (${crew_position})` : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Время начала</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${time_start ? time_start.substr(0, 5) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Время окончания</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${time_finish ? time_finish.substr(0, 5) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Тип рейса</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${flight_type || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Тип мероприятия</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${event_type_name || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Примечания</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${notes}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    // Позиционируем tooltip в правом верхнем углу, как tooltip технического обслуживания
                    newEventTooltip.style.cssText = `
                        position: fixed !important;
                        top: 0px !important;
                        left: ${canvas2.offsetWidth - 300}px !important;
                        width: 300px !important;
                        background: white !important;
                        border: 2px solid #ccc !important;
                        z-index: 999999 !important;
                        display: block !important;
                        visibility: visible !important;
                        padding: 15px !important;
                        box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
                        border-radius: 5px !important;
                    `;
                    console.log('Обновлен tooltip для мероприятия:', newEventTooltip);
                } else if (activity_type === 'FL' || activity_type === 'flight') {
                    // Получаем или создаем tooltip для рейсов
                    let newTooltip = document.getElementById('flight-tooltip');
                    if (!newTooltip) {
                        newTooltip = document.createElement('div');
                        newTooltip.id = 'flight-tooltip';
                        document.body.appendChild(newTooltip);
                    }
                    newTooltip.innerHTML = `
                        <div style="color: black; font-size: 13px; width: 100%;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tbody>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">ID</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${id || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Рейс</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${flight_number || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">ВС</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${aircraft || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Вылет</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${departure_airport || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Прилет</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${arrival_airport || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Время вылета</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${time_start ? time_start.substr(0, 5) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Время прилета</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${time_finish ? time_finish.substr(0, 5) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Факт. вылет</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${actual_time_departure ? actual_time_departure.substr(0, 5) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Факт. прилет</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">${actual_time_arrival ? actual_time_arrival.substr(0, 5) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Статус</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6; background: #F5F5F5; border-radius: 4px;">${status || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Экипаж</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">
                                            <div id="crewInfo-${id}" style="font-size: 12px;">Загрузка...</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; color: #555; font-weight: 600; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6;">Готовность</th>
                                        <td style="padding: 4px 8px; border: 1px solid #dee2e6;">
                                            <div id="readinessInfo-${id}" style="font-size: 12px;">Загрузка...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    newTooltip.style.cssText = `
                        position: fixed !important;
                        top: 50px !important;
                        left: 50px !important;
                        width: 400px !important;
                        background: white !important;
                        border: 2px solid #ccc !important;
                        z-index: 999999 !important;
                        display: block !important;
                        visibility: visible !important;
                        padding: 15px !important;
                        box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
                        border-radius: 5px !important;
                    `;
                    // Загружаем информацию об экипаже
                    loadCrewInfoForNewTooltip(id);
                    
                    // Загружаем информацию о готовности рейса
                    loadReadinessInfoForNewTooltip(id);
                    
                    console.log('Обновлен tooltip для рейса:', newTooltip);
                }
                // console.log( canvas2.offsetWidth);
                // console.log(canvas2.offsetTop);

            
                // Обновляем позицию и размеры highlightCanvas перед отрисовкой
                highlightCanvas.width = canvas2.width;
                highlightCanvas.height = canvas2.height;
                highlightCanvas.style.left = canvas2.offsetLeft + 'px';
                highlightCanvas.style.top = canvas2.offsetTop + 'px';
                
                // Очищаем предыдущую обводку
                highlightCtx.clearRect(0, 0, highlightCanvas.width, highlightCanvas.height);
                
                // Рисуем красную обводку
                highlightCtx.strokeStyle = "red";
                highlightCtx.lineWidth = 2;
                highlightCtx.strokeRect(x1stroke, y1stroke, XLongStroke, heightRectFlight1stroke);

                // console.log("status");
                
                // Весь код ниже работает со старым tooltip-container, который удален
                // Оставляем только для мероприятий (динамически созданные элементы)
                /*
                if(id != null) { document.getElementById("id").innerText=id} else {document.getElementById("id").innerText="-"}
                // Записываем статус по-русски и красим фон строки статуса
                (function(){
                    const statusEl = document.getElementById("status");
                    if (!statusEl) return;
                    const ru = STATUS_TEXTS_RU[status] || status || '-';
                    statusEl.innerText = ru;
                    // покрасим фон ячейки, где находится статус
                    try {
                        const td = statusEl.closest('td');
                        const bg = resolveCrewStatusColor(status) || '#F5F5F5';
                        if (td) {
                            td.style.backgroundColor = bg;
                            td.style.borderRadius = '4px';
                        }
                    } catch(e) {}
                })();
                
                // Если это мероприятие, отображаем специальную информацию
                if (activity_type === 'crew_events') {
                    // Показываем специальные поля для мероприятий
                    const eventInfo = document.getElementById("eventInfo");
                    const eventCrewInfo = document.getElementById("eventCrewInfo");
                    if (eventInfo) eventInfo.style.display = "block";
                    if (eventCrewInfo) eventCrewInfo.style.display = "block";
                    
                    // Отображаем тип мероприятия вместо номера рейса
                    const flightElement = document.getElementById("Flight");
                    const eventTypeElement = document.getElementById("EventType");
                    if (flight_type != null) { 
                        if (flightElement) flightElement.innerText = flight_type;
                        if (eventTypeElement) eventTypeElement.innerText = flight_type;
                    } else {
                        if (flightElement) flightElement.innerText = "Мероприятие";
                        if (eventTypeElement) eventTypeElement.innerText = "Мероприятие";
                    }
                    
                    // Отображаем информацию о назначенном сотруднике
                    const captainElement = document.getElementById("Captain");
                    const eventCrewElement = document.getElementById("EventCrew");
                    if (crew_name != null) { 
                        const crewText = crew_name + " (" + (crew_position || 'Сотрудник') + ")";
                        if (captainElement) captainElement.innerText = crewText;
                        if (eventCrewElement) eventCrewElement.innerText = crewText;
                    } else {
                        if (captainElement) captainElement.innerText = "-";
                        if (eventCrewElement) eventCrewElement.innerText = "-";
                    }
                    
                    // Для мероприятий не отображаем аэропорты
                    const depElement = document.getElementById("DEP");
                    const arrElement = document.getElementById("ARR");
                    const aircraftElement = document.getElementById("Aircraft");
                    const actualTimeStartElement = document.getElementById("ActualTimeStart");
                    const actualTimeFinishElement = document.getElementById("ActualTimeFinish");
                    
                    if (depElement) depElement.innerText = "-";
                    if (arrElement) arrElement.innerText = "-";
                    if (aircraftElement) aircraftElement.innerText = "-";
                    if (actualTimeStartElement) actualTimeStartElement.innerText = "-";
                    if (actualTimeFinishElement) actualTimeFinishElement.innerText = "-";
                } else {
                    // Скрываем специальные поля для мероприятий
                    const eventInfo = document.getElementById("eventInfo");
                    const eventCrewInfo = document.getElementById("eventCrewInfo");
                    if (eventInfo) eventInfo.style.display = "none";
                    if (eventCrewInfo) eventCrewInfo.style.display = "none";
                    
                    // Для рейсов отображаем стандартную информацию
                    const aircraftElement = document.getElementById("Aircraft");
                    const flightElement = document.getElementById("Flight");
                    const depElement = document.getElementById("DEP");
                    const arrElement = document.getElementById("ARR");
                    const captainElement = document.getElementById("Captain");
                    const actualTimeStartElement = document.getElementById("ActualTimeStart");
                    const actualTimeFinishElement = document.getElementById("ActualTimeFinish");
                    
                    if (aircraft != null && aircraftElement) { aircraftElement.innerText = aircraft} else if (aircraftElement) {aircraftElement.innerText = "-"}
                    if (flight_number != null && flightElement) { flightElement.innerText = flight_number} else if (flightElement) {flightElement.innerText = "-"}
                    if (departure_airport != null && depElement) { depElement.innerText = departure_airport} else if (depElement) {depElement.innerText = "-"}
                    if (arrival_airport != null && arrElement) { arrElement.innerText = arrival_airport} else if (arrElement) {arrElement.innerText = "-"}
                    // Отображаем информацию о членах экипажа из объекта crew
                    if (dot.crew && typeof dot.crew === 'object' && Object.keys(dot.crew).length > 0) {
                        let crewText = '';
                        for (let position in dot.crew) {
                            if (dot.crew.hasOwnProperty(position) && dot.crew[position]) {
                                let crewId = dot.crew[position];
                                let crewName = memberIdToName[crewId] || crewId || position;
                                if (crewText) crewText += ', ';
                                crewText += crewName + ' (' + position + ')';
                            }
                        }
                        if (captainElement) captainElement.innerText = crewText || '-';
                    } else if (captainElement) {
                        captainElement.innerText = '-';
                    }
                    if (actual_time_departure != null && actualTimeStartElement) {  actualTimeStartElement.innerText = actual_time_departure.substr(0, 5) } else if (actualTimeStartElement) {  actualTimeStartElement.innerText = "-"}
                    if (actual_time_arrival != null && actualTimeFinishElement) { actualTimeFinishElement.innerText = actual_time_arrival.substr(0, 5)} else if (actualTimeFinishElement) {actualTimeFinishElement.innerText = "-" }
                }
                
                // Общие поля для всех типов
                const timeStartElement = document.getElementById("TimeStart");
                const timeFinishElement = document.getElementById("TimeFinish");
                if (time_start != null && timeStartElement) { timeStartElement.innerText = time_start.substr(0, 5)} else if (timeStartElement) {timeStartElement.innerText = "-"}
                if (time_finish != null && timeFinishElement) { timeFinishElement.innerText = time_finish.substr(0, 5)} else if (timeFinishElement) {timeFinishElement.innerText = "-"}
                
                // Наполнение блока экипажа в tooltip
                try {
                    if (activity_type === 'crew_events') {
                        // Для мероприятия показываем выбранного сотрудника
                        displayCrewInfo([{ name: crew_name || '-', position: crew_position || '' }]);
                    } else {
                        // Для рейса берём crew из исходных данных
                        const currentFlightForTooltip = Object.values(data).find(f => f && f.id === id);
                        if (currentFlightForTooltip && currentFlightForTooltip.crew) {
                            displayCrewInfo(currentFlightForTooltip.crew);
                        } else {
                            displayCrewInfo(null);
                        }
                    }
                } catch (e) { } // no-op

                // Наполнение блока готовности рейса (через те же API, что и таблица)
                try {
                    const node = document.getElementById('readinessInfo');
                    if (node && activity_type !== 'crew_events') {
                        fetch(`/planning/flight-readiness/status?flight_id=${id}`)
                            .then(r => r.json())
                            .then(payload => {
                                if (!payload || !payload.success) { node.innerHTML=''; return; }
                                const readinessStatus = payload.data || {};
                                fetch('/api/readiness-types')
                                    .then(r => r.json())
                                    .then(typesPayload => {
                                        let types = [];
                                        if (typesPayload && typesPayload.success && Array.isArray(typesPayload.data)) {
                                            types = typesPayload.data;
                                        } else {
                                            types = [
                                                { id: 1, name: 'Техническая готовность' },
                                                { id: 2, name: 'Экипаж готов' },
                                                { id: 3, name: 'Пассажиры готовы' },
                                                { id: 4, name: 'Документы готовы' }
                                            ];
                                        }
                                        const makeColor = (val) => val ? '#34C759' : '#C7C7CC';
                                        const rows = types.map(t => {
                                            const s = readinessStatus[t.id] || { is_completed: false };
                                            return `
                                                <div style=\"display:flex; align-items:center; gap:8px; margin:4px 0;\">\n                                                    <span style=\"display:inline-block; width:12px; height:12px; border-radius:2px; background:${makeColor(!!s.is_completed)};\"></span>\n                                                    <span style=\"font-size:12px; color:#333;\">${t.name}</span>\n                                                </div>`;
                                        });
                                        node.innerHTML = rows.join('');
                                    })
                                    .catch(() => { node.innerHTML=''; });
                            })
                            .catch(() => { node.innerHTML=''; });
                    }
                } catch (e) { } // no-op
                */
                // Конец закомментированного блока работы со старым tooltip-container
 



    } // конец блока tooltip
    
});


const highlightCanvas = document.createElement('canvas');
highlightCanvas.width = canvas2.width;
highlightCanvas.height = canvas2.height;
highlightCanvas.style.position = 'absolute';
highlightCanvas.style.left = canvas2.offsetLeft + 'px';
highlightCanvas.style.top = canvas2.offsetTop + 'px';
highlightCanvas.style.pointerEvents = 'none'; // Чтобы события мыши проходили сквозь него
document.body.appendChild(highlightCanvas);

const highlightCtx = highlightCanvas.getContext('2d');

// Функция для очистки канваса выделения
function clearHighlight() {
    highlightCtx.clearRect(0, 0, highlightCanvas.width, highlightCanvas.height);
}




// Функция для отрисовки серого оверлея прошедшего времени
function drawPastTimeOverlay() {
    // Обновляем periodNew при каждом вызове drawPastTimeOverlay
    periodNew = period.value;
    
    // Удаляем старый canvas оверлея, если он существует
    if (pastTimeCanvas2 && pastTimeCanvas2.parentNode) {
        pastTimeCanvas2.parentNode.removeChild(pastTimeCanvas2);
    }

    // Создаем новый канвас для серого оверлея прошедшего времени
    pastTimeCanvas2 = document.createElement('canvas');
    pastTimeCanvas2.width = canvas2.width;
    pastTimeCanvas2.height = canvas2.height;
    pastTimeCanvas2.style.position = 'absolute';
    
    // Получаем точные координаты canvas2 для правильного позиционирования
    const canvas2Rect = canvas2.getBoundingClientRect();
    const documentRect = document.documentElement.getBoundingClientRect();
    
    // Позиционируем оверлей точно поверх canvas2
    pastTimeCanvas2.style.left = (canvas2Rect.left - documentRect.left) + 'px';
    pastTimeCanvas2.style.top = (canvas2Rect.top - documentRect.top) + 'px';
    pastTimeCanvas2.style.pointerEvents = 'none';
    pastTimeCanvas2.style.zIndex = '500'; // Ниже чем highlight canvas
    
    // Добавляем оверлей в document.body для правильного позиционирования
    document.body.appendChild(pastTimeCanvas2);

    pastTimeCtx2 = pastTimeCanvas2.getContext('2d');

    pastTimeCtx2.clearRect(0, 0, pastTimeCanvas2.width, pastTimeCanvas2.height);
    
    var CurrentTime = document.getElementById("CurrentTime").value;
    var currentTimeX = longFirstColumn + CurrentTime/Scale;
    const canvasEndX = canvas2.width;
    
    // Определяем начало и конец отображаемого периода
    const startDate = new Date(document.getElementById('dateQ').value);
    const endDate = new Date(startDate);
    endDate.setTime(startDate.getTime() + periodNew * 24 * 60 * 60 * 1000); // Добавляем дни в миллисекундах
    
    // Получаем текущую дату и время
    const now = new Date();
    const currentDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const currentTimeOfDay = now.getHours() * 60 + now.getMinutes(); // минуты от начала дня
    
    // Создаем полную дату и время для текущего момента
    const currentDateTime = new Date(currentDate);
    currentDateTime.setHours(Math.floor(currentTimeOfDay / 60), currentTimeOfDay % 60);
    
    pastTimeCtx2.fillStyle = 'rgba(186, 238, 195, 0.3)'; // Серый с прозрачностью 70%
    
    // Логика оверлея в зависимости от позиции текущего времени
    if (currentDateTime > endDate) {
        // Если текущее время больше "даты до" - закрашиваем все (показываем прошлое)
        pastTimeCtx2.fillRect(longFirstColumn, 50, canvas2.width - longFirstColumn, canvas2.height - 65);
    } else if (currentDateTime < startDate) {
        // Если текущее время меньше "даты с" - не рисуем оверлей
        // (ничего не делаем)
    } else {
        // Если текущее время между "датой с" и "датой до" - рисуем оверлей до линии текущего времени
        if (currentTimeX > longFirstColumn && currentTimeX <= canvasEndX) {
            pastTimeCtx2.fillRect(longFirstColumn, 50, currentTimeX - longFirstColumn, canvas2.height - 65);
        }
    }
}

canvas2.addEventListener('click', function(e) {
    const rect = canvas2.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Левая колонка (имя пилота) — обычно x < 200
    if (x < 200) {
        // Индекс пилота по y
        const row = Math.floor((y - 55) / 50); // 55 — отступ сверху, 50 — высота строки
        if (row >= 0 && row < MemberJson.length) {
            const pilot = MemberJson[row];
            // Открыть страницу редактирования в новой вкладке
        }
    }
});

// Функционал контекстного меню
let contextMenu = document.getElementById('contextMenu');
let contextMenuData = {
    x: 0,
    y: 0,
    crewMember: null,
    date: null
};

// Обработчик правого клика на canvas2
canvas2.addEventListener('contextmenu', function(e) {
    e.preventDefault(); // Предотвращаем стандартное контекстное меню
    
    const rect = canvas2.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    // console.log('Правый клик на canvas2:', { x, y, canvasWidth: canvas2.width, canvasHeight: canvas2.height });
    
    // Проверяем, что клик НЕ в верхней строке с датами (y < 50) и НЕ в левом столбце с фамилиями (x < 220)
    if (!(y < 50 && x >= 220) && !(x < 220 && y >= 50)) {
        console.log('Клик в допустимой области');
        
        // Определяем данные для контекстного меню
        let crewMember = null;
        let date = null;
        
        // Определяем ближайшую дату и члена экипажа по координатам клика
        if (y >= 50) { // Если клик в области таблицы (не в заголовке)
            // Определяем строку (член экипажа)
            const row = Math.floor((y - 50) / 50);
            
            // Получаем отфильтрованных сотрудников
            const selectedPositions = getSelectedPositionsFromStorage();
            let filteredMembers = [];
            
            if (selectedPositions.length === 0) {
                // Если не выбрано должностей, используем всех
                filteredMembers = MemberJson;
            } else {
                // Фильтруем только выбранные должности
                filteredMembers = MemberJson.filter(member => selectedPositions.includes(member.position));
            }
            
            // console.log('Определенная строка:', row, 'Filtered members length:', filteredMembers.length);
            console.log('Selected positions:', selectedPositions);
            // console.log('Filtered members:', filteredMembers);
            
            if (row >= 0 && row < filteredMembers.length) {
                crewMember = filteredMembers[row];
                console.log('Найден член экипажа:', crewMember);
            }
        }
        
        if (x >= 220) { // Если клик в области дат (не в левом столбце)
            // Определяем день
            const dayIndex = Math.floor((x - 220) / ((canvas2.width - 220) / periodNew));
            console.log('Определенный день:', dayIndex, 'periodNew:', periodNew);
            if (dayIndex >= 0 && dayIndex < periodNew) {
                const currentDate = new Date(document.getElementById('dateQ').value);
                currentDate.setDate(currentDate.getDate() + dayIndex);
                date = currentDate.toISOString().split('T')[0];
                console.log('Определенная дата:', date);
            }
        }
        
        console.log('Итоговые данные:', { crewMember, date });
        
        // Сохраняем данные контекстного меню
        contextMenuData = {
            x: e.clientX,
            y: e.clientY,
            crewMember: crewMember,
            date: date
        };
        
        // Показываем контекстное меню
        showContextMenu(e.clientX, e.clientY);
    } else {
        console.log('Клик в недопустимой области (заголовок или левый столбец)');
        // Если клик в заголовке или левом столбце, скрываем контекстное меню
        hideContextMenu();
    }
});

// Функция показа контекстного меню
function showContextMenu(x, y) {
    // Заполняем заголовок контекстного меню
    const header = document.getElementById('contextMenuHeader');
    let headerText = '';
    
    if (contextMenuData.crewMember && contextMenuData.date) {
        headerText = `${contextMenuData.crewMember.name} (${contextMenuData.crewMember.position}) - ${new Date(contextMenuData.date).toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        })}`;
    } else if (contextMenuData.crewMember) {
        headerText = `${contextMenuData.crewMember.name} (${contextMenuData.crewMember.position})`;
    } else if (contextMenuData.date) {
        const date = new Date(contextMenuData.date);
        headerText = date.toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else {
        headerText = 'Добавить элемент';
    }
    
    header.textContent = headerText;
    
    // Очищаем кэш загруженных рейсов при открытии нового контекстного меню
    const flightsList = document.getElementById('flightsList');
    if (flightsList) {
        delete flightsList.dataset.lastLoadedDate;
    }
    
    // Сначала показываем для вычисления размеров, но прячем визуально
    contextMenu.style.display = 'block';
    contextMenu.style.visibility = 'hidden';
    contextMenu.style.left = '0px';
    contextMenu.style.top = '0px';

    const menuRect = contextMenu.getBoundingClientRect();
    const margin = 10;
    const maxLeft = window.innerWidth - menuRect.width - margin;
    const maxTop = window.innerHeight - menuRect.height - margin;

    const clampedLeft = Math.max(margin, Math.min(x, maxLeft));
    const clampedTop = Math.max(margin, Math.min(y, maxTop));

    contextMenu.style.left = clampedLeft + 'px';
    contextMenu.style.top = clampedTop + 'px';
    contextMenu.style.visibility = 'visible';
}

// Универсальное позиционирование подменю в пределах вьюпорта
function positionSubmenuToViewport(submenu, anchor) {
    if (!submenu || !anchor) return;

    // Временно показываем для измерения
    submenu.style.display = 'block';
    submenu.style.visibility = 'hidden';
    submenu.style.position = 'fixed';

    const anchorRect = anchor.getBoundingClientRect();
    const submenuRect = submenu.getBoundingClientRect();
    const margin = 10;

    let left = anchorRect.right;
    let top = anchorRect.top;

    // Если не помещается справа — выводим слева
    if (left + submenuRect.width > window.innerWidth - margin) {
        left = anchorRect.left - submenuRect.width;
    }
    left = Math.max(margin, Math.min(left, window.innerWidth - submenuRect.width - margin));

    // Вертикально удерживаем в вьюпорте
    if (top + submenuRect.height > window.innerHeight - margin) {
        top = window.innerHeight - submenuRect.height - margin;
    }
    top = Math.max(margin, top);

    submenu.style.left = left + 'px';
    submenu.style.top = top + 'px';
    submenu.style.visibility = 'visible';
}

// Функция скрытия контекстного меню
function hideContextMenu() {
    contextMenu.style.display = 'none';
    // Также скрываем подменю
    const submenu = document.getElementById('flightsSubmenu');
    if (submenu) {
        submenu.style.display = 'none';
    }
    // Очищаем кэш загруженных рейсов
    const flightsList = document.getElementById('flightsList');
    if (flightsList) {
        delete flightsList.dataset.lastLoadedDate;
    }
}

// Обработчики кликов по пунктам контекстного меню
document.getElementById('addFlight').addEventListener('click', function() {
    console.log('Добавить рейс');
    console.log('Данные контекстного меню:', contextMenuData);
    
    // Здесь можно добавить логику для открытия формы добавления рейса
    // Например, открыть модальное окно или перейти на страницу создания рейса
    if (contextMenuData.crewMember) {
        // console.log('Для члена экипажа:', contextMenuData.crewMember.name);
    }
    if (contextMenuData.date) {
        // console.log('На дату:', contextMenuData.date);
    }
    
    hideContextMenu();
});

// Обработчик наведения на пункт "Добавить рейс"
document.getElementById('addFlight').addEventListener('mouseenter', function() {
    // console.log('Наведение на "Добавить рейс"');
        console.log('contextMenuData:', contextMenuData);
    
    if (contextMenuData.date) {
        // console.log('Есть дата, показываем подменю');
        const submenu = document.getElementById('flightsSubmenu');
        positionSubmenuToViewport(submenu, document.getElementById('addFlight'));
        loadFlightsForContextMenu(contextMenuData.date);
    } else {
        console.log('Нет даты в contextMenuData');
    }
});

// Обработчик ухода мыши с пункта "Добавить рейс"
document.getElementById('addFlight').addEventListener('mouseleave', function(e) {
    // Проверяем, что мышь не перешла в подменю
    const submenu = document.getElementById('flightsSubmenu');
    if (submenu && !submenu.contains(e.relatedTarget)) {
        // Если мышь не перешла в подменю, скрываем его через небольшую задержку
        setTimeout(() => {
            if (!submenu.matches(':hover') && !document.getElementById('addFlight').matches(':hover')) {
                submenu.style.display = 'none';
            }
        }, 150);
    }
});

// Обработчик наведения на подменю
document.getElementById('flightsSubmenu').addEventListener('mouseenter', function() {
    // Подменю остается видимым при наведении на него
});

// Обработчик ухода мыши с подменю
document.getElementById('flightsSubmenu').addEventListener('mouseleave', function() {
    this.style.display = 'none';
});

// Функция загрузки рейсов для контекстного меню
function loadFlightsForContextMenu(date) {
    // console.log('=== loadFlightsForContextMenu вызвана с датой:', date);
    
    // Проверка на уникальность и существование flightsList
    const allLists = document.querySelectorAll('#flightsList');
    console.log('Найдено элементов с id="flightsList":', allLists.length, allLists);
    if (allLists.length !== 1) {
        alert('Ошибка: элементов с id="flightsList" найдено ' + allLists.length + '. Должен быть только один!');
        return;
    }
    const flightsList = allLists[0];

    // console.log('Загружаем рейсы для даты:', date);

    // Убираем кэширование - всегда загружаем заново
    // if (flightsList.dataset.lastLoadedDate === date && flightsList.children.length > 0) {
    //     console.log('Рейсы уже загружены для этой даты, используем кэш');
    //     return;
    // }

    flightsList.innerHTML = '<div class="loading-flights">Загрузка рейсов...</div>';

    console.log('Отправляем запрос к API:', `/schedule/flights-by-date?date=${date}`);

    fetch(`/schedule/flights-by-date?date=${date}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Получен ответ от сервера:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('Данные от сервера:', data);

        if (data.success && data.flights.length > 0) {
            console.log('Найдено рейсов:', data.flights.length);
            
            // Отладочная информация - выводим все рейсы и их activity_type
            console.log('Все рейсы с их activity_type:');
            data.flights.forEach((flight, index) => {
                console.log(`Рейс ${index + 1}:`, {
                    flight_number: flight.flight_number,
                    activity_type: flight.activity_type,
                    type: typeof flight.activity_type
                });
            });
            
            // Фильтруем только рейсы с activity_type === 'FL'
            const filteredFlights = data.flights.filter(flight => {
                console.log(`Проверяем рейс ${flight.flight_number}: activity_type = "${flight.activity_type}" (тип: ${typeof flight.activity_type})`);
                const isFL = flight.activity_type === 'FL';
                console.log(`Результат фильтрации для ${flight.flight_number}: ${isFL}`);
                return isFL;
            });
            console.log('Отфильтровано рейсов с activity_type=FL:', filteredFlights.length);
            
            flightsList.innerHTML = '';
            
            if (filteredFlights.length > 0) {
                filteredFlights.forEach(flight => {
                    console.log('Добавляем рейс:', flight);
                    const flightItem = document.createElement('div');
                    flightItem.className = 'submenu-flight-item';
                    const departureTime = flight.time_departure ? flight.time_departure.substring(0, 5) : 'N/A';
                    const arrivalTime = flight.time_arrival ? flight.time_arrival.substring(0, 5) : 'N/A';
                    flightItem.innerHTML = `
                        <div class="flight-number">${flight.flight_number || 'N/A'}</div>
                        <div class="flight-details">
                            ${flight.aircraft || 'N/A'} | ${flight.departure_airport || 'N/A'} → ${flight.arrival_airport || 'N/A'}
                        </div>
                        <div class="flight-details">
                            ${departureTime} - ${arrivalTime}
                        </div>
                        <div class="flight-details">
                            Дата: ${flight.date_departure || 'N/A'}
                        </div>
                    `;
                    // --- ВАЖНО: обработчик внутри forEach ---
                    flightItem.addEventListener('click', function(e) {
                        e.stopPropagation();
                        console.log('Клик по рейсу:', flight);
                        showAddFlightConfirmModal(flight, contextMenuData.crewMember);
                    });
                    flightsList.appendChild(flightItem);
                });
            } else {
                flightsList.innerHTML = '<div class="no-flights">На выбранную дату рейсов типа FL не найдено</div>';
            }
            
            flightsList.dataset.lastLoadedDate = date;
        } else {
            console.log('Рейсы не найдены или ошибка в данных');
            flightsList.innerHTML = '<div class="no-flights">На выбранную дату рейсов не найдено</div>';
            flightsList.dataset.lastLoadedDate = date;
        }
        // Лог финального HTML
        console.log('HTML flightsList:', flightsList.innerHTML);
    })
    .catch(error => {
        console.error('Ошибка при загрузке рейсов:', error);
        flightsList.innerHTML = '<div class="no-flights">Ошибка при загрузке рейсов</div>';
        flightsList.dataset.lastLoadedDate = date;
    });
}

document.getElementById('addEvent').addEventListener('click', function() {
    // console.log('Добавить мероприятие0');
    // console.log('Данные контекстного меню:', contextMenuData);
    
    // Здесь можно добавить логику для открытия формы добавления мероприятия
    if (contextMenuData.crewMember) {
        console.log('Для члена экипажа:', contextMenuData.crewMember.name);
    }
    if (contextMenuData.date) {
        console.log('На дату:', contextMenuData.date);
    }
    
    hideContextMenu();
});

// --- EVENTS SUBMENU ---
// Обработчик наведения на пункт "Добавить мероприятие"
document.getElementById('addEvent').addEventListener('mouseenter', function() {
    const submenu = document.getElementById('eventsSubmenu');
    positionSubmenuToViewport(submenu, this);
    loadEventsForContextMenu();
});

// Обработчик ухода мыши с пункта "Добавить мероприятие"
document.getElementById('addEvent').addEventListener('mouseleave', function(e) {
    const submenu = document.getElementById('eventsSubmenu');
    if (submenu && !submenu.contains(e.relatedTarget)) {
        setTimeout(() => {
            if (!submenu.matches(':hover') && !document.getElementById('addEvent').matches(':hover')) {
                submenu.style.display = 'none';
            }
        }, 150);
    }
});

// Обработчик наведения на подменю мероприятий
document.getElementById('eventsSubmenu').addEventListener('mouseenter', function() {
    // Подменю остается видимым при наведении на него
});

// Обработчик ухода мыши с подменю мероприятий
document.getElementById('eventsSubmenu').addEventListener('mouseleave', function() {
    this.style.display = 'none';
});

// Функция загрузки активных мероприятий для контекстного меню
function loadEventsForContextMenu() {
    const allLists = document.querySelectorAll('#eventsList');
    if (allLists.length !== 1) {
        alert('Ошибка: элементов с id="eventsList" найдено ' + allLists.length + '. Должен быть только один!');
        return;
    }
    const eventsList = allLists[0];

    // Проверяем, не загружены ли уже мероприятия (кэш)
    if (eventsList.dataset.loaded === 'true' && eventsList.children.length > 0) {
        return;
    }

    eventsList.innerHTML = '<div class="loading-flights">Загрузка мероприятий...</div>';

    fetch('/api/active-events', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.events.length > 0) {
            eventsList.innerHTML = '';
            data.events.forEach(event => {
                const eventItem = document.createElement('div');
                eventItem.className = 'submenu-flight-item';
                const eventColor = event.color || '#007bff';
                eventItem.innerHTML = `<div class="flight-number" style="color: ${eventColor}">${event.name}</div>`;
                // TODO: добавить обработчик клика по мероприятию
                eventItem.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showAddEventAssignModal(event, contextMenuData.crewMember, contextMenuData.date);
                });
                eventsList.appendChild(eventItem);
            });
            eventsList.dataset.loaded = 'true';
        } else {
            eventsList.innerHTML = '<div class="no-flights">Нет доступных мероприятий</div>';
            eventsList.dataset.loaded = 'true';
        }
    })
    .catch(error => {
        console.error('Ошибка при загрузке мероприятий:', error);
        eventsList.innerHTML = '<div class="no-flights">Ошибка при загрузке мероприятий</div>';
        eventsList.dataset.loaded = 'true';
    });
}

// Скрытие контекстного меню при клике вне его
document.addEventListener('click', function(e) {
    const canvas2El = document.getElementById('canvas2'); // safety: ensure defined once per handler
    if (!contextMenu.contains(e.target)) {
        hideContextMenu();
    }
    // Скрываем tooltip мероприятий при клике вне его и вне canvas2
    /*
    const oldEventTooltip = document.getElementById('event-tooltip-container');
    const canvas2El = document.getElementById('canvas2');
    if (oldEventTooltip) {
        const isClickInsideTooltip = oldEventTooltip.contains(e.target);
        const isClickOnCanvas = canvas2El && canvas2El.contains(e.target);
        if (!isClickInsideTooltip && !isClickOnCanvas) {
            oldEventTooltip.style.visibility = 'hidden';
            oldEventTooltip.style.display = 'none';
        }
    }
    */
        
        // Скрываем tooltip статусов при клике вне его
        const statusTooltip = document.getElementById('status-tooltip');
        if (statusTooltip) {
            const isClickInsideStatusTooltip = statusTooltip.contains(e.target);
            const isClickOnCanvas = canvas2El && canvas2El.contains(e.target);
            if (!isClickInsideStatusTooltip && !isClickOnCanvas) {
                statusTooltip.style.visibility = 'hidden';
            }
        }
        
        // Скрываем tooltip рейсов при клике вне его
        const flightTooltip = document.getElementById('flight-tooltip');
        if (flightTooltip) {
            const isClickInsideFlightTooltip = flightTooltip.contains(e.target);
            const isClickOnCanvas = canvas2El && canvas2El.contains(e.target);
            if (!isClickInsideFlightTooltip && !isClickOnCanvas) {
                flightTooltip.style.visibility = 'hidden';
                flightTooltip.style.display = 'none';
            }
        }
        
        // Скрываем tooltip мероприятий при клике вне его
        const newEventTooltip = document.getElementById('event-tooltip');
        if (newEventTooltip) {
            const isClickInsideEventTooltip = newEventTooltip.contains(e.target);
            const isClickOnCanvas = canvas2El && canvas2El.contains(e.target);
            if (!isClickInsideEventTooltip && !isClickOnCanvas) {
                newEventTooltip.style.visibility = 'hidden';
                newEventTooltip.style.display = 'none';
        }
    }
});

// Скрытие контекстного меню при нажатии клавиши Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideContextMenu();
        // Также скрываем tooltip статусов
        const statusTooltip = document.getElementById('status-tooltip');
        if (statusTooltip) {
            statusTooltip.style.visibility = 'hidden';
        }
        // Скрываем tooltip рейсов
        const flightTooltip = document.getElementById('flight-tooltip');
        if (flightTooltip) {
            flightTooltip.style.visibility = 'hidden';
            flightTooltip.style.display = 'none';
        }
        // Скрываем tooltip мероприятий
        const eventTooltip = document.getElementById('event-tooltip');
        if (eventTooltip) {
            eventTooltip.style.visibility = 'hidden';
            eventTooltip.style.display = 'none';
        }
    }
});

// --- Модальное окно подтверждения добавления рейса сотруднику ---
function showAddFlightConfirmModal(flight, crewMember) {
    // Закрываем контекстное меню и подменю
    hideContextMenu();
    
    const modal = document.getElementById('addFlightConfirmModal');
    const text = document.getElementById('addFlightConfirmText');
    if (!flight || !crewMember) {
        text.innerHTML = '<b>Ошибка: не выбраны рейс или сотрудник!</b>';
    } else {
        text.innerHTML = `
            <b>Добавить рейс <span style="color:#007bff">${flight.flight_number}</span> сотруднику <span style="color:#007bff">${crewMember.name}</span>?</b><br>
            <span style="font-size:13px;color:#888;">${flight.departure_airport} → ${flight.arrival_airport}, ${flight.date_departure} ${flight.time_departure ? flight.time_departure.substr(0,5) : ''}</span>
        `;
    }
    modal.style.display = 'block';
    modal.dataset.flightId = flight.id;
    modal.dataset.crewId = crewMember.id;
    modal.dataset.flightNumber = flight.flight_number;
    modal.dataset.departureAirport = flight.departure_airport;
    modal.dataset.arrivalAirport = flight.arrival_airport;
    modal.dataset.dateDeparture = flight.date_departure;
    modal.dataset.timeDeparture = flight.time_departure;
    modal.dataset.crewName = crewMember.name;
}

function closeAddFlightConfirmModal() {
    document.getElementById('addFlightConfirmModal').style.display = 'none';
}

function showSelectPositionModal(flight, crewMember) {
    const modal = document.getElementById('selectPositionModal');
    const text = document.getElementById('selectPositionText');
    text.innerHTML = `
        <b>Для сотрудника <span style="color:#007bff">${crewMember.name}</span> по рейсу <span style="color:#007bff">${flight.flight_number}</span></b><br>
        <span style="font-size:13px;color:#888;">${flight.departure_airport} → ${flight.arrival_airport}, ${flight.date_departure} ${flight.time_departure ? flight.time_departure.substr(0,5) : ''}</span>
    `;
    modal.style.display = 'block';
    modal.dataset.flightId = flight.id;
    modal.dataset.crewId = crewMember.id;
    modal.dataset.flightNumber = flight.flight_number;
    modal.dataset.departureAirport = flight.departure_airport;
    modal.dataset.arrivalAirport = flight.arrival_airport;
    modal.dataset.dateDeparture = flight.date_departure;
    modal.dataset.timeDeparture = flight.time_departure;
    modal.dataset.crewName = crewMember.name;
}

function closeSelectPositionModal() {
    document.getElementById('selectPositionModal').style.display = 'none';
}

// Добавляем недостающую функцию closeFlightsModal
function closeFlightsModal() {
    document.getElementById('flightsModal').style.display = 'none';
}

// Функция назначения сотрудника на рейс
function assignCrewToFlight(flight, crew, pos) {
    console.log('assignCrewToFlight called with:', { flight, crew, pos });

    let crewName = crew.name || crew.ShortName || crew.short_name || crew.fio || crew.FullName || crew.full_name || crew.id || '???';
    let field = pos;
    let currentName = flight[field];

    console.log('Current name:', currentName);
    console.log('Crew name:', crewName);

    if (currentName && currentName !== crewName) {
        // Показываем модальное окно подтверждения замены
        if (confirm(`На позицию "${field}" уже назначен сотрудник "${currentName}". Хотите заменить его?`)) {
            flight[field] = crewName;
            updateCrewAssignmentInDB(flight.id, crew.id, field, crewName);
        }
    } else {
        flight[field] = crewName;
        updateCrewAssignmentInDB(flight.id, crew.id, field, crewName);
    }
}

// Функция обновления назначения в базе данных
function updateCrewAssignmentInDB(flightId, crewId, position, crewName) {
    console.log("Updating crew assignment:", { flightId, crewId, position, crewName });
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/planning/assign-crew', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ 
            flight_id: flightId, 
            crew_id: crewId, 
            position: position 
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert(`Сотрудник ${crewName} успешно назначен на позицию ${position}`);
            // Обновляем страницу для отображения изменений
            location.reload();
        } else {
            alert('Ошибка при назначении: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка соединения с сервером: ' + error.message);
    });
}

document.getElementById('confirmPositionBtn').addEventListener('click', function() {
    const modal = document.getElementById('selectPositionModal');
    const flightId = modal.dataset.flightId;
    const crewId = modal.dataset.crewId;
    const position = document.getElementById('positionSelect').value;
    
    // Получаем данные о рейсе и сотруднике
    const flight = {
        id: flightId,
        flight_number: modal.dataset.flightNumber,
        departure_airport: modal.dataset.departureAirport,
        arrival_airport: modal.dataset.arrivalAirport,
        date_departure: modal.dataset.dateDeparture,
        time_departure: modal.dataset.timeDeparture
    };
    
    const crewMember = {
        id: crewId,
        name: modal.dataset.crewName
    };
    
    // Вызываем функцию назначения сотрудника на рейс
    assignCrewToFlight(flight, crewMember, position);
    closeSelectPositionModal();
});

// Обновляю обработчик кнопки "Добавить" в модалке подтверждения
const confirmAddFlightBtn = document.getElementById('confirmAddFlightBtn');
confirmAddFlightBtn.addEventListener('click', function() {
    const modal = document.getElementById('addFlightConfirmModal');
    const flightId = modal.dataset.flightId;
    const crewId = modal.dataset.crewId;
    // Собираем flight и crewMember из dataset (или сохраните их глобально при открытии модалки)
    const flight = {
        id: flightId,
        flight_number: modal.dataset.flightNumber,
        departure_airport: modal.dataset.departureAirport,
        arrival_airport: modal.dataset.arrivalAirport,
        date_departure: modal.dataset.dateDeparture,
        time_departure: modal.dataset.timeDeparture
    };
    const crewMember = {
        id: crewId,
        name: modal.dataset.crewName
    };
    closeAddFlightConfirmModal();
    showSelectPositionModal(flight, crewMember);
});

// --- Модальное окно назначения мероприятия сотруднику ---
function showAddEventAssignModal(event, crewMember, defaultDate) {
    // Закрываем контекстное меню и подменю
    hideContextMenu();
    
    const modal = document.getElementById('addEventAssignModal');
    const text = document.getElementById('addEventAssignText');
    if (!event || !crewMember) {
        text.innerHTML = '<b>Ошибка: не выбраны мероприятие или сотрудник!</b>';
    } else {
        const eventColor = event.color || '#007bff';
        text.innerHTML = `
            <b>Назначить мероприятие <span style="color:${eventColor}">${event.name}</span> сотруднику <span style="color:#007bff">${crewMember.name}</span>?</b>
        `;
    }
    // Устанавливаем значения по умолчанию для дат и времени
    const startDateInput = document.getElementById('eventStartDate');
    const startTimeInput = document.getElementById('eventStartTime');
    const endDateInput = document.getElementById('eventEndDate');
    const endTimeInput = document.getElementById('eventEndTime');
    if (defaultDate) {
        startDateInput.value = defaultDate;
        endDateInput.value = defaultDate;
    } else {
        const today = new Date().toISOString().split('T')[0];
        startDateInput.value = today;
        endDateInput.value = today;
    }
    startTimeInput.value = '09:00';
    endTimeInput.value = '18:00';
    // Сохраняем данные в dataset модалки
    modal.dataset.eventId = event.id;
    modal.dataset.eventName = event.name;
    modal.dataset.crewId = crewMember.id;
    modal.dataset.crewName = crewMember.name;
    modal.style.display = 'block';
}

function closeAddEventAssignModal() {
    document.getElementById('addEventAssignModal').style.display = 'none';
}

// Обработчик кнопки Сохранить в модалке мероприятия
const confirmAddEventBtn = document.getElementById('confirmAddEventBtn');
confirmAddEventBtn.addEventListener('click', function() {
    const modal = document.getElementById('addEventAssignModal');
    const eventId = modal.dataset.eventId;
    const eventName = modal.dataset.eventName;
    const crewId = modal.dataset.crewId;
    const crewName = modal.dataset.crewName;
    const startDate = document.getElementById('eventStartDate').value;
    const startTime = document.getElementById('eventStartTime').value;
    const endDate = document.getElementById('eventEndDate').value;
    const endTime = document.getElementById('eventEndTime').value;
    const notes = (document.getElementById('eventNotes').value) ? document.getElementById('eventNotes').value : null;
    // Сохраняем мероприятие как запись в flights (activity_type=crew_events)
    fetch('/planning/crew-events/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            crew_id: crewId,
            event_id: eventId,
            event_name: eventName,
            date_start: startDate,
            time_start: startTime,
            date_end: endDate,
            time_end: endTime,
            notes: notes
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeAddEventAssignModal();
            // Показать уведомление об успехе (как на вкладке Детали рейса)
            if (typeof window.showNotification === 'function') {
                window.showNotification('Мероприятие успешно сохранено', 'success');
            } else {
                // Fallback: минимальная реализация уведомления
                try {
                    const n = document.createElement('div');
                    // Контейнер уведомления (toast в правом верхнем углу)
                    n.setAttribute('role', 'alert');
                    n.style.position = 'fixed';
                    n.style.top = '20px';
                    n.style.right = '20px';
                    n.style.zIndex = '10000';
                    n.style.maxWidth = '400px';
                    n.style.background = '#fff';
                    n.style.borderRadius = '8px';
                    n.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
                    n.style.borderLeft = '4px solid #28a745';
                    n.style.transform = 'translateX(100%)';
                    n.style.transition = 'transform 0.3s ease';

                    const content = document.createElement('div');
                    content.style.padding = '16px 20px';
                    content.style.display = 'flex';
                    content.style.alignItems = 'center';
                    content.style.justifyContent = 'space-between';
                    content.style.gap = '12px';

                    const msg = document.createElement('span');
                    msg.textContent = 'Мероприятие успешно сохранено';
                    msg.style.flex = '1';
                    msg.style.fontSize = '14px';
                    msg.style.fontWeight = '500';
                    msg.style.color = 'black';
                    msg.style.lineHeight = '1.4';

                    const btn = document.createElement('button');
                    btn.textContent = '×';
                    btn.setAttribute('aria-label', 'Close');
                    btn.style.background = 'none';
                    btn.style.border = 'none';
                    btn.style.fontSize = '20px';
                    btn.style.color = '#666';
                    btn.style.cursor = 'pointer';
                    btn.style.padding = '0';
                    btn.style.width = '24px';
                    btn.style.height = '24px';
                    btn.style.display = 'flex';
                    btn.style.alignItems = 'center';
                    btn.style.justifyContent = 'center';
                    btn.style.borderRadius = '50%';

                    btn.addEventListener('mouseenter', () => {
                        btn.style.backgroundColor = '#f8f9fa';
                        btn.style.color = '#333';
                    });
                    btn.addEventListener('mouseleave', () => {
                        btn.style.backgroundColor = 'transparent';
                        btn.style.color = '#666';
                    });

                    content.appendChild(msg);
                    content.appendChild(btn);
                    n.appendChild(content);
                    document.body.appendChild(n);
                    // Плавный выезд
                    setTimeout(() => { n.style.transform = 'translateX(0)'; }, 10);
                    // Закрытие по кнопке
                    btn.addEventListener('click', () => {
                        n.style.transform = 'translateX(100%)';
                        setTimeout(() => n.remove(), 300);
                    });
                    // Автозакрытие
                    setTimeout(() => {
                        n.style.transform = 'translateX(100%)';
                        setTimeout(() => n.remove(), 300);
                    }, 5000);
                } catch (e) {}
            }

            // Перерисовать оба канваса, если функции доступны
            if (typeof drawCanvas === 'function') {
                try { drawCanvas(); } catch (e) { /* noop */ }
            }
            if (typeof drawTable2 === 'function') {
                try { 
                    drawTable2(); 
                    window.rectHoverCrew = rectHover; // Обновляем после отрисовки
                } catch (e) { /* noop */ }
            }
            if (typeof drawCanvas !== 'function' && typeof drawTable2 !== 'function') {
                location.reload();
            }
        } else {
            alert('Ошибка при сохранении мероприятия');
        }
    })
    .catch(err => {
        console.error('Ошибка сохранения мероприятия', err);
        alert('Ошибка сохранения мероприятия');
    });
});

// Делаем все функции глобальными для использования в HTML
window.closeSelectPositionModal = closeSelectPositionModal;
window.closeAddFlightConfirmModal = closeAddFlightConfirmModal;
window.closeAddEventAssignModal = closeAddEventAssignModal;
window.closeFlightsModal = closeFlightsModal;
window.drawTable2 = drawTable2;
window.cleanCanvas = cleanCanvas;
window.getSelectedPositionsFromStorage = getSelectedPositionsFromStorage;

console.log('Функция drawTable завершена. Всего элементов в rectHover:', rectHover.length);
console.log('Элементы rectHover:', rectHover);

// Функция для загрузки информации об экипаже для нового tooltip'а
function loadCrewInfoForNewTooltip(flightId) {
    if (!flightId) {
        const crewInfoEl = document.getElementById(`crewInfo-${flightId}`);
        if (crewInfoEl) crewInfoEl.innerHTML = "Нет данных о рейсе";
        return;
    }

    // Ищем данные о рейсе в основном JSON
    const flightData = Object.values(data).find(flight => flight.id == flightId);
    
    if (flightData && flightData.crew) {
        displayCrewInfoForNewTooltip(flightData.crew, flightId);
    } else {
        const crewInfoEl = document.getElementById(`crewInfo-${flightId}`);
        if (crewInfoEl) crewInfoEl.innerHTML = "Нет данных о сотрудниках";
    }
}

// Функция для отображения информации об экипаже для нового tooltip'а
function displayCrewInfoForNewTooltip(crewData, flightId) {
    const crewInfoElement = document.getElementById(`crewInfo-${flightId}`);
    
    if (!crewInfoElement) return;
    
    let crewHTML = '';
    let hasCrewMembers = false;

    // Если пришел массив (для мероприятий crew_events)
    if (Array.isArray(crewData)) {
        crewData.forEach(member => {
            if (!member) return;
            const pos = member.position || '';
            const name = member.name || '';
            if (name.trim() !== '') {
                crewHTML += `<div><strong>${pos ? pos + ':' : ''}</strong> ${name}</div>`;
                hasCrewMembers = true;
            }
        });
        crewInfoElement.innerHTML = hasCrewMembers ? crewHTML : "Нет назначенных сотрудников";
        return;
    }

    // Если пришел объект с позициями - отображаем все должности динамически
    if (crewData && typeof crewData === 'object') {
        Object.keys(crewData).forEach(key => {
            const val = crewData[key];
            if (!val) return;
            const name = typeof val === 'string' ? val : (val.name || '');
            if (name && name.trim() !== '') {
                crewHTML += `<div><strong>${key}:</strong> ${name}</div>`;
                hasCrewMembers = true;
            }
        });
        crewInfoElement.innerHTML = hasCrewMembers ? crewHTML : "Нет назначенных сотрудников";
        return;
    }

    crewInfoElement.innerHTML = "Нет данных о сотрудниках";
}

// Функция для загрузки информации о готовности рейса для нового tooltip'а
function loadReadinessInfoForNewTooltip(flightId) {
    if (!flightId) {
        const readinessInfoEl = document.getElementById(`readinessInfo-${flightId}`);
        if (readinessInfoEl) readinessInfoEl.innerHTML = "Нет данных о рейсе";
        return;
    }

    const readinessInfoEl = document.getElementById(`readinessInfo-${flightId}`);
    if (!readinessInfoEl) return;

    // Подтягиваем реальные статусы готовности через тот же API, что и таблица
    fetch(`/planning/flight-readiness/status?flight_id=${flightId}`)
        .then(r => r.json())
        .then(payload => {
            if (!payload || !payload.success) { 
                readinessInfoEl.innerHTML=''; 
                return; 
            }
            const readinessStatus = payload.data || {};
            // Получаем справочник типов
            fetch('/api/readiness-types')
                .then(r => r.json())
                .then(typesPayload => {
                    let types = [];
                    if (typesPayload && typesPayload.success && Array.isArray(typesPayload.data)) {
                        types = typesPayload.data;
                    } else {
                        types = [
                            { id: 1, name: 'Техническая готовность' },
                            { id: 2, name: 'Экипаж готов' },
                            { id: 3, name: 'Пассажиры готовы' },
                            { id: 4, name: 'Документы готовы' }
                        ];
                    }
                    const makeColor = (val) => val ? '#34C759' : '#C7C7CC';
                    const rows = types.map(t => {
                        const s = readinessStatus[t.id] || { is_completed: false };
                        return `
                            <div style="display:flex; align-items:center; gap:8px; margin:4px 0;">
                                <span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:${makeColor(!!s.is_completed)};"></span>
                                <span style="font-size:12px; color:#333;">${t.name}</span>
                            </div>`;
                    });
                    readinessInfoEl.innerHTML = rows.join('');
                })
                .catch(() => { readinessInfoEl.innerHTML=''; });
        })
        .catch(() => { readinessInfoEl.innerHTML=''; });
}