

/**
 * Aircraft Schedule Chart - JavaScript модуль для отрисовки графика расписания воздушных судов
 * 
 * Этот файл отвечает за:
 * - Отрисовку графика полетов на canvas (первый canvas)
 * - Управление фильтрами воздушных судов (ВС)
 * - Обработку событий мыши и взаимодействие с графиком
 * - Отображение информации о рейсах и экипаже
 * - Управление временными оверлеями и выделениями
 * - Фильтрацию и отображение рейсов по выбранным ВС
 */

import 'bootstrap/dist/js/bootstrap.bundle.js';

let variant=0;
let id;
let aircraft;
let flight_number;
let activity_id;
let arrival_airport;
let departure_airport;
let time_start;
let time_finish;
let actual_time_departure;
let actual_time_arrival;    
let status;
let flight_type;
let trip_number;
let passengers_count;
let checklist_completed;
let activity_type;
let Captain;
let FO;
let PilotInstructor;
let AddCrew1;
let AddCrew2;
let SeniorFlightAttendant;
let FlightAttendant1;
let FlightAttendantInstructor;
let AviaPersonnel1;
let AviaPersonnel2;
let AviaPersonnel3;
let notes;
let aircraft_type;
let date_departure;
let date_arrival;




let hoveredObj;
let ClickCanvas=document.getElementById("canvas");



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
 //console.log("drawChange");
cleanCanvas()
drawTable()

}


let data = [];
let allAircraftsJson = [];

// Функция для инициализации данных
function initializeData() {
    try {
        data = JSON.parse(document.getElementById('data').dataset.maps);
        allAircraftsJson = JSON.parse(document.getElementById('allAircraftsJson').dataset.maps);
    } catch (e) {
        console.error('Ошибка при инициализации данных:', e);
        data = [];
        allAircraftsJson = [];
    }
}

// Инициализируем данные при загрузке
initializeData();


//console.log("allAircraftsJson",allAircraftsJson);

let checked=[];
let longFirstColumn=220;

// Глобальные переменные для canvas оверлеев
let pastTimeCanvas = null;
let pastTimeCtx = null;

let date1=document.getElementById("dateQ")
let period=document.getElementById("period")



let QuantityFL=Object.keys(data).length;

let AircraftsQuantity=Object.keys(allAircraftsJson).length; // количество ВС (до фильтра)
let AircraftsNum=[]; // массив для хранения бортовых номеров ВС (с учетом фильтра)
let AircraftsType=[]; // массив для хранения типов ВС (с учетом фильтра)

function getSelectedAircraftsFromStorage() {
    // Определяем ключ по странице
    const pathname = window.location.pathname || '';
    let keys = [];
    if (pathname.includes('crewplan')) keys.push('crewplan_aircraftFilterSelection');
    if (pathname.includes('mntplan')) keys.push('aircraftFilterSelection_mntplan');
    if (pathname.includes('schedule')) keys.push('aircraftFilterSelection_schedule');
    if (keys.length === 0) keys = ['aircraftFilterSelection_schedule','crewplan_aircraftFilterSelection','aircraftFilterSelection_mntplan'];
    for (const key of keys) {
        try {
            const saved = JSON.parse(localStorage.getItem(key) || 'null');
            if (Array.isArray(saved)) return saved;
        } catch (e) {}
    }
    // По умолчанию считаем выбранными все
    return allAircraftsJson.map(a => a.RegN);
}

function rebuildAircraftLists() {
    const selected = new Set(getSelectedAircraftsFromStorage());
    const filtered = allAircraftsJson.filter(a => selected.has(a.RegN));
   // console.log('rebuildAircraftLists',selected);
    AircraftsNum = filtered.map(a => a.RegN);
    AircraftsType = filtered.map(a => a.Type);
    AircraftsQuantity = filtered.length;
}

function refreshDataFromDataset() {
    try {
        // Обновляем основные данные
        const el = document.getElementById('data');
        if (el && el.dataset && el.dataset.maps) {
            data = JSON.parse(el.dataset.maps);
            QuantityFL = Object.keys(data).length;
            console.log('Данные обновлены в refreshDataFromDataset, количество рейсов:', QuantityFL);
        }
        
        // Обновляем данные о воздушных судах
        const allAircraftsEl = document.getElementById('allAircraftsJson');
        if (allAircraftsEl && allAircraftsEl.dataset && allAircraftsEl.dataset.maps) {
            allAircraftsJson = JSON.parse(allAircraftsEl.dataset.maps);
            console.log('Данные о ВС обновлены в refreshDataFromDataset, количество ВС:', allAircraftsJson.length);
        }
        
        // Обновляем карту цветов статусов
        const colorsNode = document.getElementById('flightStatusColors');
        if (colorsNode && colorsNode.dataset && colorsNode.dataset.maps) {
            flightStatusColorsMap = JSON.parse(colorsNode.dataset.maps);
            console.log('Карта цветов статусов обновлена:', flightStatusColorsMap);
        }
    } catch (e) {
        console.error('Ошибка в refreshDataFromDataset:', e);
    }
}

function drawCanvas() {
    // Обновляем periodNew при каждом вызове drawCanvas
    periodNew = period.value;
    console.log('drawCanvas: periodNew обновлен на:', periodNew);
    
    // Обновляем Scale на основе нового periodNew
    Scale = periodNew * 60 * 24 / (canvas.width - longFirstColumn);
    console.log('drawCanvas: Scale обновлен на:', Scale);
    
    refreshDataFromDataset();
    rebuildAircraftLists();
    cleanCanvas();
    drawTable();
}

// Делаем функцию доступной глобально
window.drawCanvas = drawCanvas;
window.drawTable = drawTable;
window.cleanCanvas = cleanCanvas;
window.rebuildAircraftLists = rebuildAircraftLists;
window.getSelectedAircraftsFromStorage = getSelectedAircraftsFromStorage;
window.refreshDataFromDataset = refreshDataFromDataset;
window.initializeData = initializeData;


const screenWidth = innerWidth-100
let canvas=document.getElementById("canvas")



canvas.width=screenWidth;
const ctx=canvas.getContext("2d");


// тултип----
// let tooltip=document.getElementById("tooltip-container")
// tooltip.style.visibility="hidden";
var canvasOffset = $("#canvas").offset();
var offsetX = canvasOffset.left;
var offsetY = canvasOffset.top;
var tipCanvas = document.getElementById("tip");
var tipCtx = tipCanvas.getContext("2d");
// тултип конец

var dot;

let heightHeader=50; // высота шапки
let heightTableString=50; // высота строки таблицы
let heightRectFlight=25; // высота прямоугольника
let numberHours; // количество часов

var currentDate=date1.value; // текущая дата
var periodNew=period.value // период

let long2=(canvas.width-longFirstColumn)/periodNew // ширина одного дня в таблице

let Scale= periodNew*60*24/(canvas.width-longFirstColumn) // масштаб (сколько минут в 1 пикселе) (количество дней*24часа*60минут/(ширина окна - первый столбец))

// ================================
// Drag-to-pan: update #dateQ by dragging canvas horizontally
// ================================
;(function enableCanvasDragPan(){
    const dateInput = document.getElementById('dateQ');
    if (!canvas || !dateInput) return;

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

    canvas.addEventListener('mousedown', onMouseDown);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', endDrag);
    canvas.addEventListener('mouseleave', endDrag);
})();

var rectHover = []; // массив для хранения прямоугольников
window.rectHover = rectHover; // делаем доступным глобально для других скриптов

// ===== Цвета статусов из справочника (если переданы со страницы) =====
const STATUS_TEXTS_RU = {
    new: 'Новый',
    confirmed: 'Подтвержден',
    daily_plan: 'В плане дня',
    in_progress: 'В процессе',
    completed: 'Завершен',
    cancelled: 'Отменен',
    delayed: 'Задержан'
};

let flightStatusColorsMap = null; // может содержать ключи как на RU, так и EN
try {
    const colorsNode = document.getElementById('flightStatusColors');
    if (colorsNode && colorsNode.dataset && colorsNode.dataset.maps) {
        flightStatusColorsMap = JSON.parse(colorsNode.dataset.maps);
    }
} catch (e) {}

function resolveStatusColorFromDict(statusKey) {
    if (!flightStatusColorsMap) return null;
    
    // Сначала ищем по точному ключу
    if (flightStatusColorsMap[statusKey]) return flightStatusColorsMap[statusKey];
    
    // Затем ищем по русскому переводу
    const ru = STATUS_TEXTS_RU[statusKey] || statusKey;
    if (flightStatusColorsMap[ru]) return flightStatusColorsMap[ru];
    
    // Ищем по нижнему регистру
    const lowerKey = statusKey ? statusKey.toLowerCase() : '';
    if (flightStatusColorsMap[lowerKey]) return flightStatusColorsMap[lowerKey];
    
    // Ищем по русскому переводу в нижнем регистре
    const lowerRu = ru ? ru.toLowerCase() : '';
    if (flightStatusColorsMap[lowerRu]) return flightStatusColorsMap[lowerRu];
    
    return null;
}


rebuildAircraftLists();
drawTable() // рисуем таблицу


function drawTable(){
    // Обновляем periodNew при каждом вызове drawTable
    periodNew = period.value;
    console.log('drawTable: periodNew обновлен на:', periodNew);
    
    // Обновляем Scale на основе нового periodNew
    Scale = periodNew * 60 * 24 / (canvas.width - longFirstColumn);
    console.log('drawTable: Scale обновлен на:', Scale);
    
let Quantity; // количество строк в таблице
let TypeData; // тип данных по сотрудникам или по ВС будем выводить данные в таблице

variant=0; Quantity=AircraftsQuantity; TypeData=AircraftsNum;


    show_flight = true;
    show_maintenance = true;
    show_measures = false;




canvas.height=(Quantity+1)*50+16;

//console.log(variant);

    let lineNumber=Quantity+1;
    let xmax=canvas.width
    let ymax=canvas.height

//рисуем шапку
    ctx.fillStyle="#1E64D4"
    ctx.fillRect(1, 1, xmax, heightHeader-1);

// пишем номера и типы  ВС в строках 

    for(let i=0; i<Quantity; i++) {
    ctx.fillStyle ="black";
    ctx.font = "17px Arial";
    ctx.fillText(TypeData[i], 10, heightHeader+heightTableString*(i+1)-20)

    ctx.font = "14px Arial";
    ctx.fillStyle ="gray";
    if(variant===0)  ctx.fillText(AircraftsType[i], 10, heightHeader+heightTableString*(i+1)-3)
   

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


        ctx.fillText(newDate1, longFirstColumn+(i1*(long))+(long/2)-(longDate.width/2)+0.5, 25)

        ctx.beginPath();
        ctx.moveTo(longFirstColumn+long*i1+0.5, 0.5);
        ctx.lineTo(longFirstColumn+long*i1+0.5, lineNumber*50+0.5);
        ctx.lineWidth = 0.5;
        ctx.strokeStyle="gray";
        ctx.stroke();



        if(periodNew==7) numberHours=4; else numberHours=8;
        for(let j=0;j<=numberHours;j++){

            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(longFirstColumn+long*i1+j*(long/numberHours)+0.5, heightHeader-5);
            ctx.lineTo(longFirstColumn+long*i1+j*(long/numberHours)+0.5, heightHeader);
            ctx.strokeStyle="white";
            ctx.stroke();


            // время
            let HoursText=0;
            if(periodNew!=30) {
            if(j!=numberHours) {
            ctx.textAlign = "center";
            ctx.font = "12px Arial";
            ctx.fillStyle="white";
            if (periodNew==7) HoursText=6; else HoursText=3;
            ctx.fillText(j*HoursText, longFirstColumn+long*i1+j*(long/numberHours)+0.5, heightHeader-10.5);
            }
            }

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

    // Проверяем, что Scale определен и CurrentTime валиден
    if (typeof Scale === 'number' && Scale > 0 && CurrentTime && !isNaN(CurrentTime)) {
        // Проверяем, попадает ли текущее время в диапазон отображаемых дат
        const currentTimeX = longFirstColumn + CurrentTime/Scale;
        const canvasEndX = canvas.width;
        
        // Рисуем линию только если текущее время попадает в диапазон
        if (currentTimeX >= longFirstColumn && currentTimeX <= canvasEndX) {
            ctx.beginPath();
            ctx.moveTo(currentTimeX, heightHeader+0.5);
            ctx.lineTo(currentTimeX, heightHeader+Quantity*50+0.5);
            ctx.strokeStyle="red";
            ctx.lineWidth=2;
            ctx.stroke();

            // Отрисовываем серый оверлей прошедшего времени            drawPastTimeOverlay();

            // рисуем треугольник на времени
            ctx.beginPath();
            ctx.moveTo(currentTimeX-5, 50);
            ctx.lineTo(currentTimeX+5, 50);
            ctx.lineTo(currentTimeX, 55);
            ctx.fillStyle="red";
            ctx.fill();
        }
    }

    rectHover.length=0;
    // рисуем прямоугольники

    rectHover.push({
        x: 0,
        y: 0,
        XLong: 0,
        heightRectFlight: 0,
        });



    for (var key in data) {


        let startFL = data[key].start
        if(startFL<0) startFL=0

        let finishFL = data[key].finish
        let aircraft = data[key].aircraft
        let activity_type = data[key].activity_type
        let flight_number = data[key].flight_number
        let id = data[key].id
        let activity_id= data[key].activity_id
        let arrival_airport = data[key].arrival_airport
        let departure_airport = data[key].departure_airport
        let time_start = data[key].time_start
        let time_finish = data[key].time_finish
        
        let status= data[key].status
        let trip_number= data[key].trip_number
        let passengers_count= data[key].passengers_count
        let checklist_completed= data[key].checklist_completed



        

        if (
            (activity_type === 'FL' && show_flight === true) ||
            (activity_type === 'maintanence' && show_maintenance === true)
          ) {
            
        rect(startFL, finishFL, aircraft, id, flight_number,activity_id, arrival_airport, departure_airport, time_start, time_finish,status,trip_number,passengers_count,checklist_completed,activity_type, data[key].flight_type)
            }

    }

    // Второй проход: перерисовываем аэропорты прилета с учетом зазоров справа
    redrawArrivalAirports();

}




function cleanCanvas()
{
    ctx.clearRect(0, 0, canvas.width, canvas.height);

}

// Функция для перерисовки аэропортов прилета с учетом зазоров справа
function redrawArrivalAirports() {
    ctx.fillStyle = "#333";
    ctx.font = "12px Arial";
    const labelYOffset = parseInt(ctx.font, 10) / 3;
    
    for (let i = 0; i < rectHover.length; i++) {
        const rect = rectHover[i];
        if (!rect || !rect.arrival_airport) continue;
        
        const XStartPrived = rect.x || 0;
        const XLong = rect.XLong || 0;
        const y = rect.y || 0;
        const heightRectFlight = rect.heightRectFlight || 25;
        const labelY = y + (heightRectFlight / 2) + labelYOffset;
        const arrival_airport = rect.arrival_airport;
        const flight_number = rect.flight_number;
        
        const arrWidth = ctx.measureText(arrival_airport).width;
        const gapLimitRight = arrWidth + 5;
        const currentRight = XStartPrived + XLong;
        const rowTol = heightRectFlight + 2;
        
        // Ищем ближайший прямоугольник справа в той же строке
        let foundNext = false;
        let minGapRight = Infinity;
        
        for (let j = 0; j < rectHover.length; j++) {
            const next = rectHover[j];
            if (!next) continue;
            
            const nextY = next.y || 0;
            const nextX = next.x || 0;
            const sameRow = Math.abs(nextY - y) <= rowTol;
            if (!sameRow) continue;
            
            // пропускаем только если это точно тот же прямоугольник (по координатам)
            const sameRect = Math.abs(nextX - XStartPrived) < 1 && Math.abs((next.XLong || 0) - XLong) < 1 && Math.abs(nextY - y) < 1;
            if (sameRect) continue;
            
            const gapRight = nextX - currentRight;
            if (gapRight >= 0 && gapRight < minGapRight) {
                minGapRight = gapRight;
                foundNext = true;
            }
        }
        
        // если справа есть блок — проверяем зазор
        let canDrawArrival = true;
        if (foundNext && minGapRight < gapLimitRight) {
            canDrawArrival = false;
        }
        
        if (canDrawArrival) {
            ctx.fillText(arrival_airport, XStartPrived + XLong + 21, labelY);
        }
    }
}



function rect(Xstart, Xfinish, aircraft, id, flight_number, activity_id, arrival_airport, departure_airport, time_start, time_finish,status,trip_number,passengers_count,checklist_completed,activity_type, flight_type) {
    let y;



    function isElement(element, index, array) {
        // Обрабатываем случаи с null значениями
        if (!aircraft || aircraft === 'null' || aircraft === null) {
            return false; // Рейсы без самолета не ищем в списке самолетов
        }
        return element === aircraft;
    }

    let Element;
    Element=AircraftsNum;
    const elementIndex = Element.findIndex(isElement);
    
    // Получаем выбранные самолеты из фильтра
    const selectedAircrafts = getSelectedAircraftsFromStorage();
    
    if (elementIndex < 0) {
        // Если самолет не найден в фильтре, но это может быть рейс без самолета
        // Попробуем найти его по номеру рейса или другим параметрам
        if (!aircraft || aircraft === 'null' || aircraft === null) {
            // Для рейсов без самолета используем специальную логику
            y = 55; // Позиция для рейсов без самолета
        } else {
            // Проверяем, выбран ли самолет в фильтре
            if (selectedAircrafts.length > 0 && !selectedAircrafts.includes(aircraft)) {
                return; // самолет не выбран фильтром — не рисуем рейс
            }
            y = 55; // Позиция для рейсов с самолетами, которых нет в списке
        }
    } else {
        // Проверяем, выбран ли самолет в фильтре
        if (selectedAircrafts.length > 0 && !selectedAircrafts.includes(aircraft)) {
            return; // самолет не выбран фильтром — не рисуем рейс
        }
        y = elementIndex*50+55;
    }
//console.log("Element",Element);
//    console.log("y",y);
    // Находим текущий полет в data по уникальному идентификатору
    // Используем id, чтобы корректно различать рейсы с одинаковым номером и бортом
    let currentFlight = Object.values(data).find(flight => flight.id == id);

    // Цвет из справочника статусов, при отсутствии — нейтральный серый
    let statusColor;
    const dictColor = resolveStatusColorFromDict(currentFlight?.status);
    statusColor = dictColor || '#F5F5F5';


    if(activity_type === 'maintanence') {
        statusColor = 'rgb(223, 94, 8)'; // Оранжевый по умолчанию
    }

  

    // Рисуем основной прямоугольник с цветом статуса
    ctx.fillStyle = statusColor;
    const XStartPrived = longFirstColumn + (Xstart/Scale);
    const XLong = (Xfinish - Xstart)/Scale;
    ctx.fillRect(XStartPrived, y, XLong, heightRectFlight);

    // Рисуем фактический прямоугольник (если есть данные)
    if(currentFlight && currentFlight.actual_date_departure && currentFlight.actual_time_departure && 
       currentFlight.actual_date_arrival && currentFlight.actual_time_arrival) {
        
        // Конвертируем дату и время в минуты от начала дня
        let actualStart = new Date(currentFlight.actual_date_departure + ' ' + currentFlight.actual_time_departure);
        let actualFinish = new Date(currentFlight.actual_date_arrival + ' ' + currentFlight.actual_time_arrival);
        
        // Вычисляем позицию относительно начала периода
        let actualXStart = (actualStart - new Date(date1.value)) / (1000 * 60);
        let actualXFinish = (actualFinish - new Date(date1.value)) / (1000 * 60);
       
        if(activity_type === 'FL') {

        // Рисуем оранжевый прямоугольник для фактического времени
        ctx.fillStyle = "rgb(255,159,10)";
        let actualXStartPrived = longFirstColumn + (actualXStart/Scale);
        let actualXLong = (actualXFinish - actualXStart)/Scale;
        ctx.fillRect(actualXStartPrived, y + heightRectFlight + 5, actualXLong, 5);
        }
    }

    rectHover.push({
        x: XStartPrived ? XStartPrived : 0,
        y: y ? y : 0,
        XLong: XLong ? XLong : 0,
        heightRectFlight: heightRectFlight ? heightRectFlight : 0,
        aircraft: aircraft ? aircraft : null,
        flight_number: flight_number ? flight_number : null,
        id: id ? id : null,
        status: status ? status : null,
        activity_id: activity_id ? activity_id : null,
        arrival_airport: arrival_airport ? arrival_airport : null,
        departure_airport: departure_airport,
        time_start: time_start,
        time_finish: time_finish,
        actual_time_departure: currentFlight ? currentFlight.actual_time_departure : null,
        actual_time_arrival: currentFlight ? currentFlight.actual_time_arrival : null,
        flight_type: flight_type,
        activity_type: activity_type,
        notes: currentFlight ? currentFlight.notes : null,
        aircraft_type: currentFlight ? currentFlight.aircraft_type : null,
        date_departure: currentFlight ? currentFlight.date_departure : null,
        date_arrival: currentFlight ? currentFlight.date_arrival : null,
        
    });

    ctx.fillStyle = "#333";
    ctx.font = "12px Arial";

    // рисуем номер рейса/тип работ по центру прямоугольника
    const labelY = y + (heightRectFlight / 2) + (parseInt(ctx.font, 10) / 3); // вертикально примерно по центру текста
    const labelText = activity_type === 'maintanence' ? flight_type : flight_number;

    if (labelText) {
        const rectWidth = XLong;
        const numberWidth = ctx.measureText(labelText).width;

        // Рассчитываем места для времени вылета/прилёта
        const time_startHM = time_start.substr(0, 5);
        const time_finishHM = time_finish.substr(0, 5);
        const depTimeWidth = ctx.measureText(time_startHM).width;
        const arrTimeWidth = ctx.measureText(time_finishHM).width;
        const padding = 8; // небольшой отступ

        const totalNeeded = numberWidth + depTimeWidth + arrTimeWidth + padding * 4; // слева/справа + между элементами
        const minNumberOnly = numberWidth + padding * 2; // номер с небольшими отступами

        if (rectWidth >= totalNeeded && activity_type === 'FL') {
            // Хватает места: рисуем всё — номер в центре, времена слева/справа
            const centeredX = XStartPrived+ rectWidth/2;
            // const centeredX = XStartPrived + rectWidth/2 - numberWidth/2;
            ctx.fillText(labelText, centeredX, labelY);

            // время вылета у левого края с отступом
            ctx.fillText(time_startHM, XStartPrived+15, labelY);
            // время прилёта у правого края с отступом
            ctx.fillText(time_finishHM, XStartPrived + XLong  - 16, labelY);
        } else if (rectWidth >= minNumberOnly) {
            // Места достаточно только для номера/типа
            const centeredX = XStartPrived + (rectWidth - numberWidth) / 2;
            ctx.fillText(labelText, centeredX, labelY);
        } else {
            // Совсем мало места — не рисуем текст
        }
    }

    // рисуем аэропорт вылета с проверкой зазора слева
    if (departure_airport) {
        const depWidth = 20;
        const gapLimit = (depWidth * 2)+40; // условие: если слева ближе чем 2 ширины названия — не рисуем
        let canDrawDeparture = true;

        // Проверяем предыдущие прямоугольники в той же строке (y совпадает)
        let foundPrev = false;
        let gapLeftLog = null;
        for (let i = rectHover.length - 1; i >= 0; i--) {
            const prev = rectHover[i];
            if (!prev) continue;
            const sameRow = Math.abs((prev.y || 0) - (y || 0)) < 1;
            if (!sameRow) continue;
            const prevEnd = (prev.x || 0) + (prev.XLong || 0);
            // учитываем только те, что полностью слева от текущего
            if (prevEnd <= XStartPrived) {
                const gapLeft = XStartPrived - prevEnd;
                gapLeftLog = gapLeft;
                if (gapLeft < gapLimit) {
                    canDrawDeparture = false;
                }
                foundPrev = true;
                break; // ближайший слева найден
            }
        }
        // Если предыдущего не найдено — всегда рисуем
        if (!foundPrev) {
            canDrawDeparture = true;
        }

        // Рисуем, если условие зазора выполнено (или предыдущего нет)
        if (canDrawDeparture) {
            const leftX = XStartPrived - depWidth;
            ctx.fillText(departure_airport, leftX, labelY);
        }

    }

    // Аэропорт прилета будет нарисован во втором проходе после отрисовки всех рейсов
    // Сохраняем информацию в rectHover для последующей перерисовки
}







$("#canvas").mousemove(function(e){handleMouseMove(e);});

// show tooltip when mouse hovers over dot
function handleMouseMove(e){
        const canvas = document.getElementById('canvas');
        if (!canvas) return; // Добавляем проверку на существование canvas


    let Quantity;
    let TypeData;
    
  
        variant=0; 
        Quantity=AircraftsQuantity; 
        TypeData=AircraftsNum; 
   

    // Получаем позицию курсора относительно canvas
    var rect = canvas.getBoundingClientRect();
    var mouseX = e.clientX - rect.left;
    var mouseY = e.clientY - rect.top;

    var hit = false;
    
    // Скрываем оба тултипа по умолчанию
   
    const aircraftStatusTooltip = document.getElementById("aircraft-status-tooltip");
    if (aircraftStatusTooltip) {
        aircraftStatusTooltip.style.visibility = "hidden";
    }
    
    // Проверяем наведение на круги статуса
    


    hoveredObj="false";
   
    // Проверяем наведение на прямоугольники рейсов
    if(!hit) {
        for (var i = 0; i < QuantityFL+1; i++) {
            dot = rectHover[i];
            // Skip if dot is undefined
            if (!dot) continue;
            
            var dx = mouseX;
            var dy = mouseY;
            /*
            console.log("i", i);

            console.log("dx", dx);
            console.log("dy", dy);
            console.log("dot.x", dot.x);
            console.log("dot.XLong", dot.XLong);
            console.log("dot.y", dot.y);
            console.log("heightRectFlight", heightRectFlight);
            console.log("dot", dot);
*/

            if((dx>dot.x) && (dx<(dot.x+dot.XLong)) && (dy>dot.y) && (dy<dot.y+heightRectFlight)) {
                
                
                aircraft=dot.aircraft;
                id=dot.id;
                flight_number=dot.flight_number;
                arrival_airport=dot.arrival_airport;
                departure_airport=dot.departure_airport;
                time_start=dot.time_start;
                time_finish=dot.time_finish;
                actual_time_departure=dot.actual_time_departure;
                actual_time_arrival=dot.actual_time_arrival;    
                status=dot.status;
                trip_number=dot.trip_number;
                activity_type=dot.activity_type;
                flight_type=dot.flight_type;
                notes=dot.notes;
                aircraft_type=dot.aircraft_type;
                date_departure=dot.date_departure;
                date_arrival=dot.date_arrival;
                console.log("status11", aircraft);
                x1stroke=dot.x;
                y1stroke=dot.y;
                XLongStroke=dot.XLong;
                heightRectFlight1stroke=heightRectFlight;
             
                // Tooltip с инфо рейса
                const tooltip = document.getElementById('aircraft-status-tooltip');
                if (tooltip) {
                    const time_startHM = (time_start || '').substr(0,5);
                    const time_finishHM = (time_finish || '').substr(0,5);

                    // предыдущий прямоугольник в той же строке слева
                    let gapText = 'Нет предыдущего';
                    let foundPrevAny = false;
                    let prevEnd = null;
                    for (let j = rectHover.length - 1; j >= 0; j--) {
                        const prev = rectHover[j];
                        if (!prev) continue;
                        const sameRow = Math.abs((prev.y || 0) - (dot.y || 0)) < 1;
                        if (!sameRow) continue;
                        const prevRight = (prev.x || 0) + (prev.XLong || 0);
                        if (prevRight <= dot.x) {
                            prevEnd = prevRight;
                            foundPrevAny = true;
                            break;
                        }
                    }
                    if (prevEnd !== null) {
                        const gapPx = dot.x - prevEnd;
                        const gapMinutes = Math.round(gapPx * (typeof Scale === 'number' ? Scale : 0));
                        gapText = gapMinutes > 0 ? `${gapMinutes} мин` : '0 мин';
                    } else if (!foundPrevAny) {
                        gapText = 'Нет предыдущего';
                    }

                    // ближайший справа для зазора справа
                    let gapRightText = 'Нет следующего';
                    let nextStart = null;
                    for (let j = 0; j < rectHover.length; j++) {
                        const next = rectHover[j];
                        if (!next) continue;
                        const sameRow = Math.abs((next.y || 0) - (dot.y || 0)) < 1;
                        if (!sameRow) continue;
                        if ((next.x || 0) >= (dot.x + dot.XLong)) {
                            nextStart = next.x;
                            break;
                        }
                    }
                    if (nextStart !== null) {
                        const gapRightPx = nextStart - (dot.x + dot.XLong);
                        const gapRightMinutes = Math.round(gapRightPx * (typeof Scale === 'number' ? Scale : 0));
                        gapRightText = gapRightMinutes > 0 ? `${gapRightMinutes} мин` : '0 мин';
                    }

                    tooltip.innerHTML = `
                        <div>Номер ВС: ${aircraft || '-'}</div>
                        <div>Номер рейса: ${flight_number || '-'}</div>
                        <div>Аэропорт вылета: ${departure_airport || '-'}</div>
                        <div>Аэропорт прилета: ${arrival_airport || '-'}</div>
                        <div>Время вылета: ${time_startHM || '-'}</div>
                        <div>Время прилета: ${time_finishHM || '-'}</div>
                   
                    `;

                    // позиционирование в пределах окна
                    tooltip.style.visibility = 'hidden';
                    tooltip.style.display = 'block';
                    tooltip.style.left = '0px';
                    tooltip.style.top = '0px';
                    const tRect = tooltip.getBoundingClientRect();
                    const margin = 10;
                    const maxLeft = window.innerWidth - tRect.width - margin;
                    const maxTop = window.innerHeight - tRect.height - margin;
                    const left = Math.max(margin, Math.min(e.clientX + 20, maxLeft));
                    const top = Math.max(margin, Math.min(e.clientY + 20, maxTop));
                    tooltip.style.left = left + 'px';
                    tooltip.style.top = top + 'px';
                    tooltip.style.visibility = 'visible';
                }

                hit = true;
                hoveredObj="true";
            } 
        }
    }
}





ClickCanvas.addEventListener('click', (event) => {
   clearHighlight();
   // const tooltipContainer = document.getElementById("tooltip-container");
   const maintenanceTooltipContainer = document.getElementById("maintenance-tooltip-container");
   
   // if (tooltipContainer) {
   //     tooltipContainer.style.visibility = "hidden";
   //     tooltipContainer.style.display = "none";
   // }
   
   if (maintenanceTooltipContainer) {
       maintenanceTooltipContainer.style.visibility = "hidden";
       maintenanceTooltipContainer.style.display = "none";
   }

   console.log("hoveredObj", hoveredObj);
    if (hoveredObj==="true") {
     
        // Проверяем тип активности
        if (activity_type === 'maintanence') {
            // Показываем tooltip для технического обслуживания
            let maintenanceTooltip = document.getElementById("maintenance-tooltip-container");
            if (maintenanceTooltip) {
                maintenanceTooltip.style.left = canvas.offsetWidth-300 + "px";
                maintenanceTooltip.style.top = 0 + "px";
                maintenanceTooltip.style.display = "block";
                maintenanceTooltip.style.visibility = "visible";
            }
        } else if (activity_type === 'crew_events') {
            // Показываем tooltip для мероприятий (как в crew-schedule-chart.js)
            let eventTooltip = document.getElementById("event-tooltip-container");
            if (eventTooltip) {
                // Позиционируем tooltip рядом с курсором, но не выходя за пределы области с днями
                const mouseX = event.clientX || 0;
                const mouseY = event.clientY || 0;
                const tooltipWidth = 400;
                const tooltipHeight = 300; // примерная высота
                
                // Получаем позицию canvas относительно документа
                const canvasRect = canvas.getBoundingClientRect();
                const canvasLeft = canvasRect.left;
                const canvasTop = canvasRect.top;
                
                // Вычисляем позицию tooltip относительно документа
                let leftPos = mouseX + 10;
                let topPos = mouseY + 10;
                
                // Ограничиваем tooltip областью с днями (начиная с longFirstColumn)
                const minLeft = canvasLeft + longFirstColumn; // Не залезаем на колонку с типами ВС
                const maxLeft = canvasLeft + canvas.width - tooltipWidth; // Не выходим за правый край
                const minTop = canvasTop; // Не выходим за верхний край
                const maxTop = canvasTop + canvas.height - tooltipHeight; // Не выходим за нижний край
                
                // Дополнительная проверка: если курсор находится в левой колонке, принудительно сдвигаем tooltip
                if (mouseX < canvasLeft + longFirstColumn) {
                    leftPos = canvasLeft + longFirstColumn + 10; // Принудительно ставим справа от колонки
                }
                
                // Корректируем позицию
                leftPos = Math.max(minLeft, Math.min(leftPos, maxLeft));
                topPos = Math.max(minTop, Math.min(topPos, maxTop));
                
                // Логирование для отладки
                console.log('Tooltip positioning:', {
                    mouseX: mouseX,
                    canvasLeft: canvasLeft,
                    longFirstColumn: longFirstColumn,
                    minLeft: minLeft,
                    finalLeft: leftPos
                });
                
                eventTooltip.style.left = leftPos + "px";
                eventTooltip.style.top = topPos + "px";
                eventTooltip.style.display = "block";
                eventTooltip.style.visibility = "visible";
            }
        } else {
            // Показываем основной tooltip для рейсов
            // let tooltip = document.getElementById("tooltip-container");
            // if (tooltip) {
            //     tooltip.style.left = canvas.offsetWidth-300 + "px";
            //     tooltip.style.top = 0 + "px";
            //     tooltip.style.display = "block";
            //     tooltip.style.visibility = "visible";
            // }
        }
                //console.log( canvas.offsetWidth);
                //console.log(canvas.offsetTop);

            
                console.log("status", status);
                
                // Обновляем позицию и размеры highlightCanvas перед отрисовкой
                highlightCanvas.width = canvas.width;
                highlightCanvas.height = canvas.height;
                highlightCanvas.style.left = canvas.offsetLeft + 'px';
                highlightCanvas.style.top = canvas.offsetTop + 'px';
                
                // Очищаем предыдущую обводку
                highlightCtx.clearRect(0, 0, highlightCanvas.width, highlightCanvas.height);
                
                // Рисуем красную обводку
                highlightCtx.strokeStyle = "red";
                highlightCtx.lineWidth = 2;
                highlightCtx.strokeRect(x1stroke, y1stroke, XLongStroke, heightRectFlight1stroke);
                console.log("x1stroke", x1stroke);
                console.log("y1stroke", y1stroke);
                console.log("XLongStroke", XLongStroke);
                console.log("heightRectFlight1stroke", heightRectFlight1stroke);

                // Заполняем данные в зависимости от типа активности
                if (activity_type === 'maintanence') {
                    // Заполняем tooltip для технического обслуживания
                    const maintenanceTypeEl = document.getElementById("maintenance-type");
                    if (maintenanceTypeEl) { if (flight_type != null) { maintenanceTypeEl.innerText = flight_type } else { maintenanceTypeEl.innerText = "-" } }
                    
                    // Формируем дату и время начала
                    let startDateTime = "-";
                    if (time_start != null && date_departure != null) {
                        const startDate = new Date(date_departure);
                        const startTime = time_start.substr(0, 5);
                        startDateTime = startDate.toLocaleDateString("ru-RU") + " " + startTime;
                    } else if (time_start != null) {
                        startDateTime = time_start.substr(0, 5);
                    }
                    const maintenanceStartEl = document.getElementById("maintenance-start");
                    if (maintenanceStartEl) { maintenanceStartEl.innerText = startDateTime; }
                    
                    // Формируем дату и время окончания
                    let finishDateTime = "-";
                    if (time_finish != null && date_arrival != null) {
                        const finishDate = new Date(date_arrival);
                        const finishTime = time_finish.substr(0, 5);
                        finishDateTime = finishDate.toLocaleDateString("ru-RU") + " " + finishTime;
                    } else if (time_finish != null) {
                        finishDateTime = time_finish.substr(0, 5);
                    }
                    const maintenanceFinishEl = document.getElementById("maintenance-finish");
                    if (maintenanceFinishEl) { maintenanceFinishEl.innerText = finishDateTime; }
                    
                    const maintenanceAircraftTypeEl = document.getElementById("maintenance-aircraft-type");
                    if (maintenanceAircraftTypeEl) { if (aircraft_type != null) { maintenanceAircraftTypeEl.innerText = aircraft_type } else { maintenanceAircraftTypeEl.innerText = "-" } }
                    
                    const maintenanceAircraftEl = document.getElementById("maintenance-aircraft");
                    if (maintenanceAircraftEl) { if (aircraft != null) { maintenanceAircraftEl.innerText = aircraft } else { maintenanceAircraftEl.innerText = "-" } }
                    
                    const maintenanceLocationEl = document.getElementById("maintenance-location");
                    if (maintenanceLocationEl) { if (departure_airport != null) { maintenanceLocationEl.innerText = departure_airport } else { maintenanceLocationEl.innerText = "-" } }
                    
                    const maintenanceNotesEl = document.getElementById("maintenance-notes");
                    if (maintenanceNotesEl) { if (notes != null) { maintenanceNotesEl.innerText = notes } else { maintenanceNotesEl.innerText = "-" } }
                } else if (activity_type === 'crew_events') {
                    // Заполняем tooltip для мероприятий
                    // Данные для мероприятий заполняются в event-tooltip-container
                    // (этот блок может быть пустым, так как данные заполняются в crew-schedule-chart.js)
                } else {
                    // Заполняем основной tooltip для рейсов
                    const aircraftEl = document.getElementById("Aircraft");
                    if (aircraftEl) {
                        if (aircraft != null) { aircraftEl.innerText=aircraft} else {aircraftEl.innerText="-"}
                    }
                }
                // Перевод статуса и фон ячейки в цвет статуса
                (function(){
                    const statusEl = document.getElementById("status");
                    if (!statusEl) return;
                    const ru = STATUS_TEXTS_RU[status] || status || '-';
                    statusEl.innerText = ru;
                    try {
                        const td = statusEl.closest('td');
                        const bg = resolveStatusColorFromDict(status) || '#F5F5F5';
                        if (td) {
                            td.style.backgroundColor = bg;
                            td.style.borderRadius = '4px';
                        }
                    } catch(e) {}
                })();
                const idEl = document.getElementById("id");
                if (idEl) { if (id != null) { idEl.innerText=id} else {idEl.innerText="-"} }
                
                const flightEl = document.getElementById("Flight");
                if (flightEl) { if (flight_number != null) { flightEl.innerText=flight_number} else {flightEl.innerText="-"} }
                
                const depEl = document.getElementById("DEP");
                if (depEl) { if (departure_airport != null) { depEl.innerText=departure_airport} else {depEl.innerText="-"} }
                
                const arrEl = document.getElementById("ARR");
                if (arrEl) { if (arrival_airport != null) { arrEl.innerText=arrival_airport} else {arrEl.innerText="-"} }
                
                const timeStartEl = document.getElementById("TimeStart");
                if (timeStartEl) { if (time_start != null) { timeStartEl.innerText=time_start.substr(0, 5)} else {timeStartEl.innerText="-"} }
                
                const timeFinishEl = document.getElementById("TimeFinish");
                if (timeFinishEl) { if (time_finish != null) { timeFinishEl.innerText=time_finish.substr(0, 5)} else {timeFinishEl.innerText="-"} }
                
                const actualTimeStartEl = document.getElementById("ActualTimeStart");
                if (actualTimeStartEl) { if (actual_time_departure != null) { actualTimeStartEl.innerText=actual_time_departure.substr(0, 5) } else { actualTimeStartEl.innerText="-"} }
                
                const actualTimeFinishEl = document.getElementById("ActualTimeFinish");
                if (actualTimeFinishEl) { if (actual_time_arrival != null) { actualTimeFinishEl.innerText=actual_time_arrival.substr(0, 5)} else {actualTimeFinishEl.innerText="-" } }

                // Загружаем информацию о сотрудниках
                loadCrewInfo(id);
                // Готовность рейса (если есть данные)
                (function(){
                    const node = document.getElementById('readinessInfo');
                    if (!node) return;
                    // Подтягиваем реальные статусы готовности через тот же API, что и таблица
                    fetch(`/planning/flight-readiness/status?flight_id=${id}`)
                        .then(r => r.json())
                        .then(payload => {
                            if (!payload || !payload.success) { node.innerHTML=''; return; }
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
                                            <div style=\"display:flex; align-items:center; gap:8px; margin:4px 0;\">\n                                                <span style=\"display:inline-block; width:12px; height:12px; border-radius:2px; background:${makeColor(!!s.is_completed)};\"></span>\n                                                <span style=\"font-size:12px; color:#333;\">${t.name}</span>\n                                            </div>`;
                                    });
                                    node.innerHTML = rows.join('');
                                })
                                .catch(() => { node.innerHTML=''; });
                        })
                        .catch(() => { node.innerHTML=''; });
                })();

    }
});

// Hide tooltips on outside click (outside canvas)
document.addEventListener('click', (event) => {
    const canvasEl = document.getElementById('canvas');
    // const tooltipEl = document.getElementById('tooltip-container');
    const tooltipEl = null; // safety: tooltip-container removed
    const eventTooltipEl = document.getElementById('event-tooltip-container');
    const maintenanceTooltipEl = document.getElementById('maintenance-tooltip-container');

    // Если клик внутри самого тултипа — не скрываем
    const clickedInsideTooltip = (
        // (tooltipEl && tooltipEl.contains(event.target)) ||
        (eventTooltipEl && eventTooltipEl.contains(event.target)) ||
        (maintenanceTooltipEl && maintenanceTooltipEl.contains(event.target))
    );
    if (clickedInsideTooltip) return;

    // Клик по канвасу обрабатывается отдельной логикой — не скрываем тут
    if (canvasEl && canvasEl.contains(event.target)) return;

    if (tooltipEl) tooltipEl.style.visibility = 'hidden';
    if (eventTooltipEl) eventTooltipEl.style.visibility = 'hidden';
    if (maintenanceTooltipEl) maintenanceTooltipEl.style.visibility = 'hidden';
});


const highlightCanvas = document.createElement('canvas');
highlightCanvas.width = canvas.width;
highlightCanvas.height = canvas.height;
highlightCanvas.style.position = 'absolute';
highlightCanvas.style.left = canvas.offsetLeft + 'px';
highlightCanvas.style.top = canvas.offsetTop + 'px';
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
    if (pastTimeCanvas && pastTimeCanvas.parentNode) {
        pastTimeCanvas.parentNode.removeChild(pastTimeCanvas);
    }

    // Получаем точные координаты canvas для правильного позиционирования
    const canvasRect = canvas.getBoundingClientRect();
    const documentRect = document.documentElement.getBoundingClientRect();

    // Создаем новый канвас для серого оверлея прошедшего времени
    pastTimeCanvas = document.createElement('canvas');
    pastTimeCanvas.width = canvas.width;
    pastTimeCanvas.height = canvas.height;
    pastTimeCanvas.style.position = 'absolute';
    pastTimeCanvas.style.left = (canvasRect.left - documentRect.left) + 'px';
    pastTimeCanvas.style.top = (canvasRect.top - documentRect.top) + 'px';
    pastTimeCanvas.style.pointerEvents = 'none';
    pastTimeCanvas.style.zIndex = '1'; // Ниже чем highlight canvas
    document.body.appendChild(pastTimeCanvas);

    pastTimeCtx = pastTimeCanvas.getContext('2d');

    pastTimeCtx.clearRect(0, 0, pastTimeCanvas.width, pastTimeCanvas.height);
    
    var CurrentTime = document.getElementById("CurrentTime").value;
    var currentTimeX = longFirstColumn + CurrentTime/Scale;
    const canvasEndX = canvas.width;
    
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
    
    pastTimeCtx.fillStyle = 'rgba(186, 238, 195, 0.3)'; // Серый с прозрачностью 70%
    
    // Логика оверлея в зависимости от позиции текущего времени
    if (currentDateTime > endDate) {
        // Если текущее время больше "даты до" - закрашиваем все (показываем прошлое)
        pastTimeCtx.fillRect(longFirstColumn, 50, canvas.width - longFirstColumn, canvas.height-65);
    } else if (currentDateTime < startDate) {
        // Если текущее время меньше "даты с" - очищаем оверлей (рисуем с шириной 1px)
        pastTimeCtx.fillRect(longFirstColumn, 50, 1, canvas.height - 65);
    } else {
        // Если текущее время между "датой с" и "датой до" - рисуем оверлей до линии текущего времени
        if (currentTimeX > longFirstColumn && currentTimeX <= canvasEndX) {
            pastTimeCtx.fillRect(longFirstColumn, 50, currentTimeX - longFirstColumn, canvas.height-65);
        }
    }
}



// Функция для загрузки информации о сотрудниках
function loadCrewInfo(flightId) {
    if (!flightId) {
        document.getElementById("crewInfo").innerHTML = "Нет данных о рейсе";
        return;
    }

    // Ищем данные о рейсе в основном JSON
    const flightData = Object.values(data).find(flight => flight.id == flightId);
    
    if (flightData && flightData.crew) {
        displayCrewInfo(flightData.crew);
    } else {
        document.getElementById("crewInfo").innerHTML = "Нет данных о сотрудниках";
    }
}

// Функция для отображения информации о сотрудниках
function displayCrewInfo(crewData) {
    const crewInfoElement = document.getElementById("crewInfo");
    
    if (!crewInfoElement) return;
    
    // Порядок должностей для отображения
    const positionOrder = ['КВС', 'ВП', 'ПИ', 'СБП', 'БП1', 'БП2', 'ИТП1', 'ИТП2', 'ИТП3'];
    
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

    // Если пришел объект с позициями
    if (crewData && typeof crewData === 'object') {
        // Сначала по заданному порядку
        positionOrder.forEach(position => {
            const val = crewData[position];
            if (!val) return;
            const name = typeof val === 'string' ? val : (val.name || '');
            if (name && name.trim() !== '') {
                crewHTML += `<div><strong>${position}:</strong> ${name}</div>`;
                hasCrewMembers = true;
            }
        });
        // Затем остальные ключи
        Object.keys(crewData).forEach(key => {
            if (positionOrder.includes(key)) return;
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