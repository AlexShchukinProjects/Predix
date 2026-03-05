console.log("RectMovement.js loaded");

// Ожидаем загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log("RectMovement.js DOMContentLoaded event fired");

// Функция для получения выбранных должностей из DOM
function getSelectedPositionsFromDOM() {
    try {
        // Ищем чекбоксы должностей на странице
        const positionCheckboxes = document.querySelectorAll('.position-checkbox:checked');
        const selectedPositions = Array.from(positionCheckboxes).map(cb => cb.value);
        
        console.log('=== getSelectedPositionsFromDOM debug ===');
        console.log('Found position checkboxes:', positionCheckboxes.length);
        console.log('Selected positions:', selectedPositions);
        
        return selectedPositions;
    } catch (e) {
        console.error('Error getting positions from DOM:', e);
        return [];
    }
}

// Функция для получения отфильтрованных сотрудников
function getFilteredMembers() {
    // Пытаемся использовать глобальные переменные из crew-schedule-chart.js
    // Эти переменные обновляются в drawTable2() и содержат актуальный список отфильтрованных сотрудников
    if (typeof window.filteredMembers !== 'undefined' && Array.isArray(window.filteredMembers) && window.filteredMembers.length > 0) {
        console.log('Using window.filteredMembers from crew-schedule-chart.js:', window.filteredMembers.length);
        return window.filteredMembers;
    }
    
    // Fallback: используем старую логику
    const selectedPositions = getSelectedPositionsFromDOM();
    let MemberJson;
    try {
        const memberJsonElement = document.getElementById('MemberJson');
        if (!memberJsonElement) {
            console.error('MemberJson element not found!');
            return [];
        }
        MemberJson = JSON.parse(memberJsonElement.dataset.maps);
    } catch (error) {
        console.error('Error parsing MemberJson:', error);
        return [];
    }
    
    console.log('=== getFilteredMembers debug ===');
    console.log('Selected positions from DOM:', selectedPositions);
    console.log('All members:', MemberJson);
    console.log('Member positions:', MemberJson.map(m => m.position));
    
    if (selectedPositions.length === 0) {
        // Если не выбрано должностей, возвращаем всех
        console.log('No positions selected, returning all members');
        return MemberJson;
    } else {
        // Фильтруем только выбранные должности
        const filtered = MemberJson.filter(member => selectedPositions.includes(member.position));
        console.log('Filtered members:', filtered);
        return filtered;
    }
}

let Canvas=document.getElementById("canvas");
let Canvas2=document.getElementById("canvas2");
let RectMovement=document.getElementById("RectMovement");
let period=document.getElementById("period");

// Проверяем, что все необходимые элементы существуют
console.log('=== Element Check ===');
console.log('Canvas:', Canvas);
console.log('Canvas2:', Canvas2);
console.log('RectMovement:', RectMovement);
console.log('period:', period);

// Дополнительная проверка элементов
console.log('Canvas exists:', !!Canvas);
console.log('Canvas2 exists:', !!Canvas2);
console.log('RectMovement exists:', !!RectMovement);
console.log('period exists:', !!period);

// Проверяем все элементы с этими ID на странице
console.log('All canvas elements:', document.querySelectorAll('canvas'));
console.log('All elements with id canvas:', document.querySelectorAll('#canvas'));
console.log('All elements with id canvas2:', document.querySelectorAll('#canvas2'));
console.log('All elements with id RectMovement:', document.querySelectorAll('#RectMovement'));
console.log('All elements with id period:', document.querySelectorAll('#period'));

if (!Canvas) {
    console.error('Canvas element not found!');
} else if (!Canvas2) {
    console.error('Canvas2 element not found!');
} else if (!RectMovement) {
    console.error('RectMovement element not found!');
} else if (!period) {
    console.error('Period element not found!');
} else {
    console.log('All elements found, initializing RectMovement...');
    // Все элементы найдены, продолжаем инициализацию
    initializeRectMovement();
}

function initializeRectMovement() {
    console.log('=== initializeRectMovement started ===');
    console.log('Canvas element:', Canvas);
    console.log('Canvas2 element:', Canvas2);
    console.log('RectMovement element:', RectMovement);
    console.log('period element:', period);

var periodNew=period.value;
let longFirstColumn=220;
let heightHeader=50; // высота шапки
let Scale= periodNew*60*24/(Canvas.width-longFirstColumn)

console.log('Scale calculation:', 'periodNew =', periodNew, 'Canvas.width =', Canvas.width, 'longFirstColumn =', longFirstColumn, 'Scale =', Scale);

// Проверяем, что MemberJson существует
let MemberJson;
try {
    const memberJsonElement = document.getElementById('MemberJson');
    if (memberJsonElement) {
        MemberJson = JSON.parse(memberJsonElement.dataset.maps);
    } else {
        console.error('MemberJson element not found!');
        MemberJson = [];
    }
} catch (error) {
    console.error('Error parsing MemberJson:', error);
    MemberJson = [];
}
let flight_number;
let id;
let crew = null;
let crewPosition = null;

// --- Помощники для учета выбранных ВС из фильтра ---
function getSelectedAircraftsFromDOM() {
    try {
        // Ищем отмеченные чекбоксы воздушных судов на странице
        const aircraftCheckboxes = document.querySelectorAll('.aircraft-checkbox:checked');
        const selectedAircrafts = Array.from(aircraftCheckboxes).map(cb => cb.value);
        
        console.log('=== getSelectedAircraftsFromDOM debug ===');
        console.log('Found aircraft checkboxes:', aircraftCheckboxes.length);
        console.log('Selected aircrafts:', selectedAircrafts);
        
        return selectedAircrafts;
    } catch (e) {
        console.error('Error getting aircrafts from DOM:', e);
        return [];
    }
}

// Функция показа tooltip с информацией о рейсе
function showFlightTooltip(flightData) {
    console.log('=== showFlightTooltip called ===');
    console.log('Flight data:', flightData);
    
    const tooltip = document.getElementById("tooltip-container");
    if (!tooltip) {
        console.error('Tooltip container not found!');
        return;
    }
    
    // Заполняем tooltip данными о рейсе
    if (flightData.aircraft) document.getElementById("Aircraft").innerText = flightData.aircraft;
    if (flightData.status) document.getElementById("status").innerText = flightData.status;
    if (flightData.id) document.getElementById("id").innerText = flightData.id;
    if (flightData.flight_number) document.getElementById("Flight").innerText = flightData.flight_number;
    if (flightData.departure_airport) document.getElementById("DEP").innerText = flightData.departure_airport;
    if (flightData.arrival_airport) document.getElementById("ARR").innerText = flightData.arrival_airport;
    if (flightData.time_start) document.getElementById("TimeStart").innerText = flightData.time_start.substr(0, 5);
    if (flightData.time_finish) document.getElementById("TimeFinish").innerText = flightData.time_finish.substr(0, 5);
    if (flightData.actual_time_departure) document.getElementById("ActualTimeStart").innerText = flightData.actual_time_departure.substr(0, 5);
    if (flightData.actual_time_arrival) document.getElementById("ActualTimeFinish").innerText = flightData.actual_time_arrival.substr(0, 5);
    
    // Показываем tooltip
    tooltip.style.left = Canvas.offsetWidth - 300 + "px";
    tooltip.style.top = 0 + "px";
    tooltip.style.visibility = "visible";
    
    console.log('Tooltip should be visible now');
}

// Устанавливаем размеры canvas равными размерам основного canvas + canvas2
RectMovement.width = Canvas.offsetWidth;
RectMovement.height = Canvas.offsetHeight + Canvas2.offsetHeight;

RectMovement.style.zIndex = '9999'; // Увеличиваем z-index
RectMovement.style.position = 'absolute';
RectMovement.style.pointerEvents = 'none';
RectMovement.style.left = Canvas.offsetLeft + 'px';
RectMovement.style.top = Canvas.offsetTop + 'px';
RectMovement.style.width = Canvas.offsetWidth + 'px';
RectMovement.style.height = (Canvas.offsetHeight + Canvas2.offsetHeight) + 'px';
RectMovement.style.backgroundColor = 'transparent'; // Убеждаемся, что фон прозрачный

// Добавляем отладочную информацию
//console.log('Canvas initialization:');
//console.log('Canvas offsetLeft:', Canvas.offsetLeft, 'offsetTop:', Canvas.offsetTop);
//console.log('Canvas2 offsetLeft:', Canvas2.offsetLeft, 'offsetTop:', Canvas2.offsetTop);
//console.log('RectMovement position:', RectMovement.style.left, RectMovement.style.top);
//console.log('RectMovement size:', RectMovement.style.width, RectMovement.style.height); 
//console.log('RectMovement z-index:', RectMovement.style.zIndex);

//  console.log('Canvas height:', Canvas.offsetHeight);
//  console.log('Canvas2 height:', Canvas2.offsetHeight);
//  console.log('RectMovement height:', RectMovement.height);

// Проверяем, что canvas правильно инициализирован
//console.log('RectMovement width/height:', RectMovement.width, 'x', RectMovement.height);
//console.log('RectMovement offsetWidth/Height:', RectMovement.offsetWidth, 'x', RectMovement.offsetHeight);

// Переменные для перетаскивания
let isDragging = false;
let rectX = 100;
let rectY = 100;
let rectWidth = 50; // Ширина красного прямоугольника
let rectHeight = 25; // Высота красного прямоугольника
let startY = 0;
let rectVisible = false; // Флаг видимости прямоугольника
let dragThreshold = 3; // Порог в пикселях для появления прямоугольника
let totalDragY = 0; // Общее расстояние перетаскивания
let autoScrollInterval = null; // Интервал для автоматического скролла
const SCROLL_ZONE = 100; // Зона в пикселях от края экрана, где начинается скролл
const SCROLL_SPEED = 5; // Скорость скролла в пикселях за итерацию
let lastScrollY = 0; // Последняя позиция скролла для отслеживания изменений

// Убираем начальную отрисовку прямоугольника
// drawRect();

// Обработчики событий переносим на основной canvas
// Убираем конфликтующий click обработчик - оставляем только mousedown
console.log("Click event listener removed to avoid conflicts");

// Обработчик нажатия мыши
console.log('Adding mousedown event listener to Canvas:', Canvas);
Canvas.addEventListener("mousedown", function(e) {
    console.log("=== Mouse down event triggered ===");
    console.log("Button:", e.button);
    console.log("Canvas element:", Canvas);
    
    if (e.button === 0) { // Левая кнопка мыши
        // Обновляем Scale при каждом клике, так как период может измениться
        let currentPeriod = period.value;
        let currentScale = currentPeriod * 60 * 24 / (Canvas.width - longFirstColumn);
        console.log('Updated Scale calculation:', 'periodNew =', currentPeriod, 'Canvas.width =', Canvas.width, 'longFirstColumn =', longFirstColumn, 'Scale =', currentScale);
        
        let rect = Canvas.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;
      
        console.log('Mouse click at coordinates:', x, y);
        // Проверяем, попал ли клик в один из прямоугольников рейсов
        let clickedOnFlight = false;
        
        // Получаем данные о рейсах из основного canvas
        let data;
        try {
            const dataElement = document.getElementById('data');
            if (dataElement) {
                data = JSON.parse(dataElement.dataset.maps);
            } else {
                console.error('Data element not found!');
                return; // Этот return допустим, так как он внутри функции
            }
        } catch (error) {
            console.error('Error parsing flight data:', error);
            return; // Этот return допустим, так как он внутри функции
        }

        console.log(data, "datasetrectmovement");
        console.log('Number of flights:', Object.keys(data).length);
        console.log('Flight data keys:', Object.keys(data));
        
        // Отладочная информация о доступных элементах данных
        console.log('Available data elements:');
        console.log('- allAircraftsJson:', document.getElementById('allAircraftsJson') ? 'found' : 'not found');
        console.log('- CaptainsJson:', document.getElementById('CaptainsJson') ? 'found' : 'not found');
        console.log('- btnradio1:', document.getElementById('btnradio1') ? 'checked: ' + document.getElementById('btnradio1').checked : 'not found');
        
        if (document.getElementById('allAircraftsJson')) {
            console.log('- allAircraftsJson content:', JSON.parse(document.getElementById('allAircraftsJson').dataset.maps));
        }
        if (document.getElementById('CaptainsJson')) {
            console.log('- CaptainsJson content:', JSON.parse(document.getElementById('CaptainsJson').dataset.maps));
        }

        const QuantityFL = Object.keys(data).length;

        
        // Проверяем попадание в каждый прямоугольник рейса
        let selectedFlightData = null; // Сохраняем данные выбранного рейса
        for (let i = 0; i < QuantityFL; i++) {
            // Получаем координаты прямоугольника рейса (нужно вычислить как в app.js)
            let flightData = data[i];
            if (flightData) {
                let XStartPrived = 220 + (flightData.start / currentScale);
                let XLong = (flightData.finish - flightData.start) / currentScale;
                
                // Находим Y координату для этого рейса (используем ту же логику, что и в app.js)
                let flightY = 0;
             
                    // Для воздушных судов — учитываем выбранные в фильтре ВС
                    const allAircraftsElement = document.getElementById('allAircraftsJson');
                    if (allAircraftsElement) {
                        const allAircraftsJson = JSON.parse(allAircraftsElement.dataset.maps);
                        const selected = new Set(getSelectedAircraftsFromDOM());
                        const filteredAircrafts = allAircraftsJson.filter(a => selected.has(a.RegN));
                        console.log('Filtered aircrafts (by selection):', filteredAircrafts);
                        console.log('Looking for aircraft:', flightData.aircraft);
                        let aircraftIndex = filteredAircrafts.findIndex(element => element.RegN === flightData.aircraft);
                        if (aircraftIndex === -1) {
                            // Этот рейс относится к невыбранному ВС — игнорируем
                            console.warn('Aircraft not selected in filter:', flightData.aircraft);
                            continue;
                        }
                        flightY = aircraftIndex * 50 + 55;
                        console.log('aircraftIndex', aircraftIndex, 'flightY:', flightY);
                    } else {
                        console.error('allAircraftsJson element not found!');
                        continue; // Пропускаем этот рейс
                    }
               
                
                console.log('Checking flight', i, ':', flightData.flight_number);
                console.log('Flight bounds:', XStartPrived, XStartPrived + XLong, flightY, flightY + 25);
                console.log('Click position:', x, y);
                
                // Проверяем попадание в прямоугольник рейса
                let hitX = (x >= XStartPrived && x <= XStartPrived + XLong);
                let hitY = (y >= flightY && y <= flightY + 25);
                console.log('Hit check - hitX:', hitX, 'hitY:', hitY, 'x range:', XStartPrived, 'to', XStartPrived + XLong, 'y range:', flightY, 'to', flightY + 25);
                
                if (hitX && hitY) {
                    // Проверяем, что это не техническое обслуживание
                    if (flightData.activity_type === 'maintanence') {
                        console.log('Clicked on maintenance - drag not allowed');
                        clickedOnFlight = false; // Не разрешаем перетаскивание
                        break;
                    }
                    
                    clickedOnFlight = true;
                    flight_number = flightData.flight_number;
                    id = flightData.id;
                    selectedFlightData = flightData; // Сохраняем данные рейса

                    console.log('HIT! Clicked on flight:', flight_number);
                    console.log(id, "id");
                    // Устанавливаем размеры и позицию синего прямоугольника равными рейсу
                    rectX = XStartPrived; // X координата рейса
                    rectY = flightY; // Y координата рейса (используем вычисленную координату рейса)
                    
                    // Если рейс начинается левее первой колонки, обрезаем его
                    if (XStartPrived < longFirstColumn) {
                        rectWidth = XLong - (longFirstColumn - XStartPrived); // Уменьшаем ширину
                        rectX = longFirstColumn; // Сдвигаем вправо до первой колонки
                    } else {
                        rectWidth = XLong; // Обычная ширина
                    }
                    
                    rectHeight = 25; // Высота рейса
                    
                    console.log("XStartPrived", XStartPrived);
                    console.log("rectY set to flightY:", flightY);

                    break;
                }
            }
        }
        
        console.log('Final result - clickedOnFlight:', clickedOnFlight);
        
        // Начинаем перетаскивание только если кликнули по рейсу
        if (clickedOnFlight) {
            console.log('=== Flight clicked - starting drag ===');
            console.log('Flight data:', flight_number, 'ID:', id);
            console.log('Rectangle position before:', rectX, rectY, rectWidth, rectHeight);
            
            isDragging = true;
            startY = e.clientY;
            totalDragY = 0;
            lastScrollY = window.pageYOffset || document.documentElement.scrollTop; // Инициализируем позицию скролла
            
            // НЕ перезаписываем rectY - используем уже установленную позицию рейса
            console.log('Using existing rectY:', rectY);
            
            // Показываем синий прямоугольник сразу при нажатии на рейс
            rectVisible = true;
            console.log('Setting rectVisible to true');
            drawRect();
            
            // Показываем tooltip с информацией о рейсе
            showFlightTooltip(selectedFlightData);
            
            // Предотвращаем выделение текста
            e.preventDefault();
            
            console.log('Started dragging - isDragging:', isDragging, 'startY:', startY, 'rectY:', rectY);
            console.log('Blue rectangle should be visible now');
        } else {
            console.log('Clicked outside flight - hiding rectangle');
            // Если кликнули не по рейсу, скрываем прямоугольник
            if (rectVisible) {
                rectVisible = false;
                drawRect();
                console.log('Rectangle hidden - clicked outside flight');
            }
        }
    }
});

// Функция для остановки автоматического скролла
function stopAutoScroll() {
    if (autoScrollInterval) {
        clearInterval(autoScrollInterval);
        autoScrollInterval = null;
        console.log('Auto-scroll stopped');
    }
}

// Функция для запуска автоматического скролла
function startAutoScroll(direction) {
    // Останавливаем предыдущий скролл, если он был
    stopAutoScroll();
    
    console.log('Starting auto-scroll:', direction);
    
    // Запускаем новый скролл
    autoScrollInterval = setInterval(function() {
        if (!isDragging) {
            // Если перетаскивание закончилось, останавливаем скролл
            stopAutoScroll();
            return;
        }
        
        const scrollAmount = direction === 'down' ? SCROLL_SPEED : -SCROLL_SPEED;
        const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
        const maxScrollY = document.documentElement.scrollHeight - window.innerHeight;
        
        // Проверяем, можем ли еще скроллить
        if (direction === 'down' && currentScrollY >= maxScrollY) {
            stopAutoScroll();
            return;
        }
        if (direction === 'up' && currentScrollY <= 0) {
            stopAutoScroll();
            return;
        }
        
        const scrollDelta = scrollAmount;
        window.scrollBy(0, scrollDelta);
        
        // При скролле просто перерисовываем прямоугольник
        // Позиция будет обновлена в обработчике mousemove на основе текущей позиции курсора
        if (rectVisible) {
            drawRect();
        }
        
        // Обновляем последнюю позицию скролла
        lastScrollY = currentScrollY + scrollDelta;
    }, 16); // ~60 FPS
}

// Обработчик движения мыши
document.addEventListener("mousemove", function(e) {
    if (isDragging) {
        // Предотвращаем выделение текста
        e.preventDefault();
        
        // Получаем позицию курсора относительно canvas
        const canvasRect = Canvas.getBoundingClientRect();
        const mouseY = e.clientY - canvasRect.top;
        
        // Просто используем позицию курсора для определения Y координаты прямоугольника
        if (rectVisible) {
            rectY = mouseY;
            
            // Ограничиваем движение в пределах canvas
            let maxY = Canvas.offsetHeight + Canvas2.offsetHeight - 50;
            if (rectY > maxY) {
                rectY = maxY;
            }
            if (rectY < 0) {
                rectY = 0;
            }
            
            console.log('Rectangle position:', rectX, rectY, 'mouseY:', mouseY);
        }
        
        // Проверяем, нужно ли включить автоматический скролл
        const viewportHeight = window.innerHeight;
        const mouseYViewport = e.clientY; // Позиция курсора относительно viewport
        const distanceFromTop = mouseYViewport;
        const distanceFromBottom = viewportHeight - mouseYViewport;
        
        console.log('Auto-scroll check - mouseY:', mouseY, 'distanceFromBottom:', distanceFromBottom, 'distanceFromTop:', distanceFromTop);
        
        // Останавливаем скролл, если курсор не в зоне скролла
        if (distanceFromBottom > SCROLL_ZONE && distanceFromTop > SCROLL_ZONE) {
            stopAutoScroll();
        } else {
            // Определяем направление скролла
            if (distanceFromBottom <= SCROLL_ZONE) {
                // Скроллим вниз
                console.log('Starting scroll down - distanceFromBottom:', distanceFromBottom);
                startAutoScroll('down');
            } else if (distanceFromTop <= SCROLL_ZONE) {
                // Скроллим вверх
                console.log('Starting scroll up - distanceFromTop:', distanceFromTop);
                startAutoScroll('up');
            }
        }
        
        // Обновляем startY для следующей итерации
        startY = e.clientY;
        
        drawRect();
    } else {
        // Если не перетаскиваем, останавливаем скролл
        stopAutoScroll();
    }
});

// Обработчик события скролла (для отслеживания ручного скролла)
window.addEventListener("scroll", function() {
    if (isDragging && rectVisible) {
        // При скролле просто перерисовываем прямоугольник
        // Позиция будет обновлена в обработчике mousemove на основе текущей позиции курсора
        drawRect();
    }
}, { passive: true });

// Обработчик отпускания мыши
document.addEventListener("mouseup", function(e) {
    // Останавливаем автоматический скролл при отпускании мыши
    stopAutoScroll();
    
    if (isDragging) {
        // Получаем позицию курсора относительно страницы
        let mouseX = e.clientX;
        let mouseY = e.clientY;
        
        // Получаем позицию canvas2 относительно страницы
        let canvas2Rect = Canvas2.getBoundingClientRect();
        
        // Проверяем, находится ли курсор над canvas2
        let isOnCanvas2 = mouseX >= canvas2Rect.left && mouseX <= canvas2Rect.right &&
                         mouseY >= canvas2Rect.top && mouseY <= canvas2Rect.bottom;
        
        //  console.log('Mouse position:', mouseX, mouseY);
        //  console.log('Canvas2 bounds:', canvas2Rect);
        //  console.log('Is on Canvas2:', isOnCanvas2);
        //  console.log('MemberJson:', MemberJson);
        
        if (isOnCanvas2) {
            // Вычисляем позицию относительно canvas2
            let relativeY = mouseY - canvas2Rect.top;
            
            // Получаем отфильтрованных сотрудников
            const filteredMembers = getFilteredMembers();
            console.log('Filtered members count:', filteredMembers.length);
            console.log('Relative Y to Canvas2:', relativeY);
            console.log('Canvas2 rect:', canvas2Rect);
            
            // Определяем row сотрудника по Y
            // Из crew-schedule-chart.js: 
            // - heightHeader = 50 (высота шапки)
            // - heightTableString = 50 (высота строки)
            // - Горизонтальные линии рисуются на позициях: i*heightTableString (0, 50, 100, 150...)
            // - Строка 0 (первая строка сотрудника): от 50px до 100px
            // - Строка 1 (вторая строка сотрудника): от 100px до 150px
            // - Формула: row = Math.floor((relativeY - heightHeader) / heightTableString)
            const headerHeight = 50; // Высота шапки (heightHeader)
            const rowHeight = 50; // Высота строки сотрудника (heightTableString)
            
            // Если клик в шапке, пропускаем
            if (relativeY < headerHeight) {
                console.log('Click in header area (relativeY <', headerHeight, '), ignoring');
                return;
            }
            
            // Вычисляем row: (relativeY - headerHeight) / rowHeight
            // relativeY = 50-99: row = 0 (первая строка)
            // relativeY = 100-149: row = 1 (вторая строка)
            let row = Math.floor((relativeY - headerHeight) / rowHeight);
            
            // Дополнительная проверка: если row выходит за пределы, возможно проблема в структуре canvas2
            // На продакшн может быть другая структура, поэтому делаем более мягкую проверку
            
            console.log('Calculated row:', row, 'from relativeY:', relativeY);
            console.log('Filtered members length:', filteredMembers.length);
            console.log('Available rows range: 0 to', filteredMembers.length - 1);
            
            // Проверяем, что row в допустимых пределах
            if (row >= 0 && row < filteredMembers.length) {
                crew = filteredMembers[row];
                crewPosition = crew.position || 'CAP'; // Используем позицию из данных или по умолчанию
                console.log('Selected crew:', crew);
                console.log('Crew position:', crewPosition);
                
                let flight = id;
                
                // Показываем модальное окно и вызываем handleAddCrewToFlight
                if (flight && crew) {
                    // flight — это ID, ищем объект:
                    const data = JSON.parse(document.getElementById('data').dataset.maps);
                    let flightObj = Object.values(data).find(f => f.id == flight);
                    if (flightObj) {
                        console.log('Found flight object:', flightObj);
                        showAddFlightModal(() => handleAddCrewToFlight(flightObj, crew));
                    } else {
                        console.log('Flight object not found for ID:', flight);
                        console.log('Available flight IDs:', Object.values(data).map(f => f.id));
                        alert('Не удалось найти данные рейса');
                    }
                } else {
                    console.log('Flight ID:', flight);
                    console.log('Crew:', crew);
                    alert('Не удалось найти рейс или сотрудника');
                }
            } else {
                console.error('Invalid row calculation:');
                console.error('- Calculated row:', row);
                console.error('- Filtered members length:', filteredMembers.length);
                console.error('- Relative Y to Canvas2:', relativeY);
                console.error('- Canvas2 rect:', canvas2Rect);
                console.error('- Mouse Y:', mouseY);
                console.error('- Available rows:', Array.from({length: filteredMembers.length}, (_, i) => i));
                console.error('- Filtered members:', filteredMembers.map((m, idx) => ({index: idx, name: m.ShortName || m.short_name || m.name, position: m.position})));
                
                // Пытаемся найти ближайшую валидную строку
                if (row < 0 && filteredMembers.length > 0) {
                    console.warn('Row is negative, using first row (0)');
                    row = 0;
                } else if (row >= filteredMembers.length && filteredMembers.length > 0) {
                    console.warn('Row exceeds array length, using last row');
                    row = filteredMembers.length - 1;
                }
                
                // Если после коррекции row валиден, используем его
                if (row >= 0 && row < filteredMembers.length) {
                    console.log('Using corrected row:', row);
                    crew = filteredMembers[row];
                    crewPosition = crew.position || 'CAP';
                    console.log('Selected crew:', crew);
                    console.log('Crew position:', crewPosition);
                    
                    let flight = id;
                    if (flight && crew) {
                        const data = JSON.parse(document.getElementById('data').dataset.maps);
                        let flightObj = Object.values(data).find(f => f.id == flight);
                        if (flightObj) {
                            console.log('Found flight object:', flightObj);
                            showAddFlightModal(() => handleAddCrewToFlight(flightObj, crew));
                        } else {
                            console.log('Flight object not found for ID:', flight);
                            alert('Не удалось найти данные рейса');
                        }
                    } else {
                        alert('Не удалось найти рейс или сотрудника');
                    }
                } else {
                    alert('Неверная позиция для назначения сотрудника. Row: ' + row + ', Members: ' + filteredMembers.length);
                }
            }
        } else {
            console.log('Dropped outside Canvas2');
        }
        
        // Скрываем синий прямоугольник в любом случае
        rectVisible = false;
        drawRect();
        console.log('Blue rectangle hidden');
    }
    isDragging = false;
    totalDragY = 0;
});

// Функция отрисовки прямоугольника
function drawRect() {
    console.log('=== drawRect function called ===');
    //  console.log('rectVisible:', rectVisible);
    //  console.log('RectMovement element:', RectMovement);
    //  console.log('RectMovement style:', RectMovement.style.cssText);
    //  console.log('RectMovement dimensions:', RectMovement.width, 'x', RectMovement.height);
    //  console.log('RectMovement offset:', RectMovement.offsetLeft, RectMovement.offsetTop);
    
    let ctx = RectMovement.getContext("2d");
    console.log('Canvas context:', ctx);
    
    ctx.clearRect(0, 0, RectMovement.width, RectMovement.height); // Очищаем canvas
    console.log('Canvas cleared');
    
    // Рисуем прямоугольник только если он должен быть видим
    if (rectVisible) {
        // console.log('Drawing rectangle - rectVisible is true');
        
        // Делаем прямоугольник синим, как изначально просили
        ctx.fillStyle = "rgba(0, 0, 255, 0.7)"; // Синий с прозрачностью
        ctx.fillRect(rectX, rectY, rectWidth, rectHeight);
       // console.log('Rectangle filled at:', rectX, rectY, 'size:', rectWidth, 'x', rectHeight);
        
        // Добавляем синюю обводку
        ctx.strokeStyle = "blue";
        ctx.lineWidth = 2;
        ctx.strokeRect(rectX, rectY, rectWidth, rectHeight);
       // console.log('Rectangle stroke added');
        
        // console.log('Drawing BLUE rectangle at:', rectX, rectY, 'size:', rectWidth, 'x', rectHeight);
        // console.log('Canvas context:', ctx);
        // console.log('Canvas dimensions:', RectMovement.width, 'x', RectMovement.height);
    } else {
        console.log('Not drawing rectangle - rectVisible is false');
    }

    console.log('drawRect completed - visible:', rectVisible, 'totalDrag:', totalDragY, 'canvas size:', RectMovement.width, 'x', RectMovement.height);
}

// Функция показа модального окна
function showAddFlightModal(onYes) {
    console.log('showAddFlightModal called with onYes function:', typeof onYes);
    // Создаем модальное окно
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    `;
    
    modalContent.innerHTML = `
        <h3 style="margin-bottom: 20px; color: #333;">Добавить рейс для данного сотрудника?</h3>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="modal-yes" style="
                padding: 10px 25px;
                background: #28a745;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            ">ДА</button>
            <button id="modal-no" style="
                padding: 10px 25px;
                background: #dc3545;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            ">НЕТ</button>
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Обработчики кнопок
    document.getElementById('modal-yes').addEventListener('click', function() {
        console.log('Пользователь выбрал ДА - добавляем рейс');
        console.log('onYes function type:', typeof onYes);
        modal.remove();
        rectVisible = false;
        drawRect();
        if (onYes) {
            console.log('Calling onYes function');
            onYes();
        } else {
            console.error('onYes function is not defined!');
        }
    });
    
    document.getElementById('modal-no').addEventListener('click', function() {
        console.log('Пользователь выбрал НЕТ');
        modal.remove();
        rectVisible = false;
        drawRect();
    });
    
    // Закрытие по клику вне модального окна
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            rectVisible = false;
            drawRect();
        }
    });
}





// Эти функции теперь определены глобально после initializeRectMovement()

// Проверяем, что все обработчики событий добавлены
console.log("RectMovement.js initialization completed");
console.log("Event listeners added:");
console.log("- Canvas click event");
console.log("- Canvas mousedown event");
console.log("- Document mousemove event");
console.log("- Document mouseup event");

// Дополнительная проверка - убеждаемся, что обработчики действительно добавлены
setTimeout(() => {
    console.log("Final check - Canvas element:", Canvas);
    console.log("Final check - Canvas2 element:", Canvas2);
    console.log("Final check - RectMovement element:", RectMovement);
    console.log("Final check - isDragging variable exists:", typeof isDragging !== 'undefined');
}, 1000);

} // Закрывающая скобка для функции initializeRectMovement()

// Глобальные функции для использования в других местах

// --- Модалка выбора позиции ---
function showPositionModal(flight, crew, onAssign) {
    console.log('showPositionModal called with:', { flight, crew, onAssign });
    // Будем хранить ICAO типа ВС для дальнейшей проверки требований
    let aircraftTypeIcaoForChecks = null;

    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10001;
    `;
    
    // Показываем загрузку
    modal.innerHTML = `
        <div style="background:#fff;padding:30px;border-radius:10px;min-width:320px;text-align:center">
            <h4>Выберите позицию сотрудника</h4>
            <p style="color:#666;margin-bottom:15px;">Сотрудник: ${crew.ShortName || crew.short_name || crew.fio || crew.name || 'Неизвестно'}</p>
            <p style="color:#666;margin-bottom:15px;">Загрузка должностей для данного типа ВС...</p>
        </div>
    `;
    document.body.appendChild(modal);
    
    /**
     * ДАЛЕЕ:
     * 1) Берём регистрацию борта из объекта рейса (flight.aircraft / flight.Aircraft)
     * 2) По регистрации получаем ID типа ВС: /api/aircraft-type-by-regn?regn=REGN
     * 3) По типу ВС получаем минимальный экипаж: /api/minimum-crew/by-aircraft-type?aircraft_type_id=ID
     * 4) Строим список должностей только из этих записей (quantity > 0)
     */

    // 1. Пытаемся определить регистрацию борта
    const regn = flight.aircraft || flight.Aircraft || flight.RegN || null;
    console.log('Detected aircraft regn for minimum crew filter:', regn, 'from flight:', flight);

    if (!regn) {
        console.error('Не удалось определить регистрацию ВС из данных рейса, показываем все должности');
        // Фолбэк на старое поведение: просто закрываем модалку с ошибкой
        modal.innerHTML = `
            <div style="background:#fff;padding:30px;border-radius:10px;min-width:320px;text-align:center">
                <h4>Ошибка</h4>
                <p style="color:#dc3545;margin-bottom:15px;">Не удалось определить тип воздушного судна для данного рейса</p>
                <button id="position-close" style="
                    padding: 8px 20px;
                    background: #6c757d;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">Закрыть</button>
            </div>
        `;
        document.getElementById('position-close').onclick = function() {
            modal.remove();
        };
        return;
    }

    // 2. Получаем ID типа ВС по регистрации
    fetch(`/api/aircraft-type-by-regn?regn=${encodeURIComponent(regn)}`)
        .then(response => {
            console.log('aircraft-type-by-regn response status:', response.status);
            return response.json();
        })
        .then(typeData => {
            console.log('aircraft-type-by-regn response data:', typeData);

            if (!typeData.success || !typeData.aircraft_type_id) {
                throw new Error(typeData.message || 'Не удалось определить тип воздушного судна');
            }

            const aircraftTypeId = typeData.aircraft_type_id;
            // Пытаемся определить ICAO типа ВС для сопоставления с данными статусов требований
            aircraftTypeIcaoForChecks = typeData.aircraft_type_icao || typeData.icao || typeData.code || null;
            console.log('Resolved aircraft_type_id:', aircraftTypeId);
            console.log('Resolved aircraft_type_icao for checks:', aircraftTypeIcaoForChecks);

            // 3. Получаем минимальный экипаж для типа ВС
            return fetch(`/api/minimum-crew/by-aircraft-type?aircraft_type_id=${aircraftTypeId}`);
        })
        .then(response => {
            console.log('minimum-crew/by-aircraft-type response status:', response.status);
            return response.json();
        })
        .then(minCrewData => {
            console.log('=== RAW API RESPONSE ===');
            console.log('minimum-crew/by-aircraft-type response data:', JSON.stringify(minCrewData, null, 2));

            if (!minCrewData.success || !minCrewData.data) {
                throw new Error(minCrewData.message || 'Не удалось получить минимальный состав экипажа');
            }

            // minCrewData.data — это объект групп по типам персонала:
            // { "Летный экипаж": [...], "Кабинный экипаж": [...], "ИТП": [...] }
            const grouped = minCrewData.data;
            
            console.log('=== Minimum Crew Data Structure ===');
            console.log('Grouped data (full):', JSON.stringify(grouped, null, 2));
            console.log('Group keys:', Object.keys(grouped));
            Object.keys(grouped).forEach(key => {
                console.log(`=== Group "${key}" ===`);
                console.log(`Group "${key}" (full):`, JSON.stringify(grouped[key], null, 2));
                if (Array.isArray(grouped[key])) {
                    console.log(`Group "${key}" has ${grouped[key].length} items`);
                    grouped[key].forEach((item, idx) => {
                        console.log(`  === Item ${idx} in group "${key}" ===`);
                        console.log(`  Full item:`, JSON.stringify(item, null, 2));
                        console.log(`  Item keys:`, Object.keys(item));
                        console.log(`  item.quantity:`, item.quantity, '| type:', typeof item.quantity, '| parsed:', parseInt(item.quantity) || 0);
                        console.log(`  item.position_name:`, item.position_name);
                        console.log(`  item.position_short_name:`, item.position_short_name);
                    });
                } else {
                    console.log(`Group "${key}" is NOT an array! Type:`, typeof grouped[key]);
                }
            });

            // 3.1. Определяем тип экипажа текущего сотрудника по его должности
            return fetch('/api/positions')
                .then(resp => resp.json())
                .then(posData => {
                    let crewTypeFilter = null;
                    if (posData.success && Array.isArray(posData.data)) {
                        const crewPosCode =
                            crew.position ||
                            crew.Position ||
                            crew.position_name ||
                            crew.PositionName ||
                            crew.Pos ||
                            null;

                        console.log('Trying to resolve crew_type for crew position:', crewPosCode);

                        if (crewPosCode) {
                            const matchedPosition = posData.data.find(p =>
                                p.short_name === crewPosCode || p.Name === crewPosCode
                            );
                            if (matchedPosition && matchedPosition.crew_type) {
                                crewTypeFilter = matchedPosition.crew_type; // 'Летный экипаж', 'Кабинный экипаж', 'ИТП'
                            }
                            console.log('Matched position:', matchedPosition, 'crewTypeFilter:', crewTypeFilter);
                        }
                    }

                    const positions = [];
                    const groupKeys = crewTypeFilter ? [crewTypeFilter] : Object.keys(grouped);

                    groupKeys.forEach(groupKey => {
                        const groupItems = grouped[groupKey] || [];
                        console.log(`Processing group "${groupKey}" with ${groupItems.length} items`);
                        console.log(`Full groupItems array:`, JSON.stringify(groupItems, null, 2));
                        
                        groupItems.forEach((item, itemIndex) => {
                            console.log(`=== Processing item ${itemIndex} ===`);
                            console.log(`Full item object:`, JSON.stringify(item, null, 2));
                            console.log(`Item keys:`, Object.keys(item));
                            console.log(`item.quantity raw:`, item.quantity, `type:`, typeof item.quantity);
                            
                            // Преобразуем quantity в число на случай, если оно пришло как строка
                            const quantity = parseInt(item.quantity) || 0;
                            console.log(`Position: ${item.position_name}, quantity (parsed): ${quantity} (original type: ${typeof item.quantity}, original value: ${item.quantity})`);
                            
                            // Временная проверка для отладки
                            if (item.position_name === 'Пилот' && quantity !== 2) {
                                console.error('ERROR: Пилот quantity should be 2, but got:', quantity);
                                alert('ERROR: Пилот quantity=' + quantity + ', expected 2!');
                            }
                            
                            // Берём только должности с количеством > 0
                            if (quantity > 0) {
                                console.log(`  Creating ${quantity} options for position "${item.position_name}"`);
                                
                                // Создаем столько опций, сколько указано в quantity
                                for (let i = 1; i <= quantity; i++) {
                                    console.log(`  LOOP: i=${i}, quantity=${quantity}, condition: ${i <= quantity}`);
                                    const positionShortName = item.position_short_name || item.position_name;
                                    const positionName = item.position_name;
                                    
                                    // Формируем текст опции в формате "short_name - position_name" (как было раньше)
                                    // Для должностей с quantity > 1 добавляем номер к position_name
                                    let displayText;
                                    if (quantity > 1) {
                                        // Если несколько позиций, добавляем номер: "F/O - Пилот 1", "F/O - Пилот 2"
                                        displayText = positionShortName && positionShortName !== positionName
                                            ? `${positionShortName} - ${positionName} ${i}`
                                            : `${positionName} ${i}`;
                                    } else {
                                        // Если одна позиция, используем стандартный формат: "F/O - Пилот"
                                        displayText = positionShortName && positionShortName !== positionName
                                            ? `${positionShortName} - ${positionName}`
                                            : positionName;
                                    }
                                    
                                    // Значение для отправки на сервер (со слотом, если quantity > 1)
                                    const value = quantity > 1 
                                        ? `${positionShortName}${i}` 
                                        : positionShortName;
                                    
                                    console.log(`  Creating option ${i}/${quantity}: "${displayText}" (value: "${value}")`);
                                    
                                    positions.push({
                                        value: value,
                                        text: displayText,
                                    });
                                }
                            } else {
                                console.log(`  Skipping position "${item.position_name}" - quantity is 0 or invalid (parsed: ${quantity})`);
                            }
                        });
                    });

                    console.log('Positions built from minimum crew with crew_type filter:', positions);

                    if (positions.length === 0) {
                        throw new Error('Для данного типа воздушного судна и категории персонала не настроены должности в минимальном составе экипажа');
                    }

                    let options = '';
                    positions.forEach(position => {
                        options += `<option value="${position.value}">${position.text}</option>`;
                    });

                    // Обновляем модальное окно с отфильтрованными должностями
                    modal.innerHTML = `
                        <div style="background:#fff;padding:30px;border-radius:10px;min-width:320px;text-align:center">
                            <h4>Выберите позицию сотрудника</h4>
                            <p style="color:#666;margin-bottom:15px;">Сотрудник: ${crew.ShortName || crew.short_name || crew.fio || crew.name || 'Неизвестно'}</p>
                            <select id="crew-position-select" class="form-select mb-3" style="width:100%;padding:8px;margin-bottom:15px;">
                                <option value="">Выберите должность</option>
                                ${options}
                            </select>
                            <div style="display:flex;gap:10px;justify-content:center;">
                                <button id="position-ok" style="
                                    padding: 8px 20px;
                                    background: #007bff;
                                    color: white;
                                    border: none;
                                    border-radius: 5px;
                                    cursor: pointer;
                                ">OK</button>
                                <button id="position-cancel" style="
                                    padding: 8px 20px;
                                    background: #6c757d;
                                    color: white;
                                    border: none;
                                    border-radius: 5px;
                                    cursor: pointer;
                                ">Отмена</button>
                            </div>
                        </div>
                    `;

                    // Добавляем обработчики после обновления HTML
                    document.getElementById('position-ok').onclick = function() {
                        console.log('Position OK button clicked');
                        const pos = document.getElementById('crew-position-select').value;
                        if (!pos) {
                            alert('Пожалуйста, выберите должность');
                            return;
                        }
                        console.log('Selected position:', pos);
                        
                        // Перед назначением выполняем проверку требований для данного сотрудника и типа ВС
                        console.log('=== REQUIREMENTS CHECK START ===');
                        console.log('Crew object in RectMovement:', crew);
                        console.log('aircraftTypeIcaoForChecks in RectMovement:', aircraftTypeIcaoForChecks);
                        
                        try {
                            // Глобальная карта статусов сотрудников формируется в crew-schedule-chart.js
                            const globalCrewStatusMap = (typeof window !== 'undefined' && window.crewStatusMap) ? window.crewStatusMap : null;
                            console.log('window.crewStatusMap exists:', !!globalCrewStatusMap);
                            
                            // Определяем ID сотрудника (в MemberJson он точно называется id, см. crew-schedule-chart.js)
                            const crewIdForStatus = crew && (crew.id || crew.ID || crew.crew_id || crew.person_id || null);
                            console.log('Resolved crewIdForStatus:', crewIdForStatus);
                            
                            if (globalCrewStatusMap && crewIdForStatus) {
                                const crewStatus = globalCrewStatusMap[crewIdForStatus];
                                console.log('Found crewStatus for requirements check:', crewStatus);
                                
                                if (crewStatus && Array.isArray(crewStatus.aircraft_types) && crewStatus.aircraft_types.length > 0) {
                                    let typeStatus = null;
                                    
                                    // Если удалось определить ICAO типа ВС для рейса — ищем точное совпадение
                                    if (aircraftTypeIcaoForChecks) {
                                        typeStatus = crewStatus.aircraft_types.find(t => t.aircraft_type_icao === aircraftTypeIcaoForChecks);
                                        console.log('Matched typeStatus by ICAO:', typeStatus);
                                    }
                                    
                                    // Если точное сопоставление не найдено, берем первый доступный тип как фолбэк
                                    if (!typeStatus) {
                                        console.warn('Type-specific status for aircraft not found, using first available aircraft_type entry for crew');
                                        typeStatus = crewStatus.aircraft_types[0];
                                    }
                                    
                                    if (typeStatus && Array.isArray(typeStatus.requirements)) {
                                        console.log('typeStatus for requirements check:', typeStatus);
                                        
                                        // Отбираем требования со статусом "истек" или "истекает"
                                        const problemRequirements = typeStatus.requirements.filter(r => {
                                            return r && (r.status === 'expired' || r.status === 'warning' || r.status === 'expiring');
                                        });
                                        
                                        console.log('problemRequirements for this assignment:', problemRequirements);
                                        
                                        if (problemRequirements.length > 0) {
                                            // Показываем модальное окно с предупреждением о требованиях
                                            showRequirementsWarningModal(
                                                crew,
                                                aircraftTypeIcaoForChecks || (typeStatus.aircraft_type_icao || '-'),
                                                problemRequirements,
                                                function() {
                                                    // Пользователь подтвердил назначение несмотря на предупреждения
                                                    modal.remove();
                                                    console.log('Calling onAssign with position after requirements warning:', pos);
                                                    onAssign(pos);
                                                }
                                            );
                                            // Не закрываем модалку выбора позиции до решения пользователя в модалке предупреждения
                                            return;
                                        }
                                    } else {
                                        console.log('typeStatus is missing or has no requirements array');
                                    }
                                }
                            } else {
                                console.log('No globalCrewStatusMap or crewIdForStatus not resolved; skipping requirements warning');
                            }
                        } catch (err) {
                            console.error('Error while checking crew requirements before assignment:', err);
                            // В случае ошибки проверки требований продолжаем обычное назначение
                        }

                        // Если проблемных требований нет или проверка не удалась — продолжаем обычное назначение
                        modal.remove();
                        console.log('Calling onAssign with position (no blocking requirements):', pos);
                        onAssign(pos);
                    };

                    document.getElementById('position-cancel').onclick = function() {
                        modal.remove();
                    };

                    modal.onclick = e => { if (e.target === modal) modal.remove(); };
                });
        })
        .catch(error => {
            console.error('Error while loading positions for minimum crew:', error);
            modal.innerHTML = `
                <div style="background:#fff;padding:30px;border-radius:10px;min-width:320px;text-align:center">
                    <h4>Ошибка</h4>
                    <p style="color:#dc3545;margin-bottom:15px;">${error.message || 'Ошибка при загрузке должностей для данного типа ВС'}</p>
                    <p style="color:#666;font-size:12px;margin-bottom:15px;">Проверьте настройки минимального состава экипажа и тип ВС для борта "${regn}".</p>
                    <button id="position-close" style="
                        padding: 8px 20px;
                        background: #6c757d;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                    ">Закрыть</button>
                </div>
            `;
            document.getElementById('position-close').onclick = function() {
                modal.remove();
            };
        });
}

// --- Модалка подтверждения замены ---
function showReplaceModal(field, currentName, onReplace) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10001;
    `;
    
    // Преобразуем название поля в читаемый вид
    let fieldDisplay = field;
    switch(field) {
        case 'КВС': fieldDisplay = 'КВС'; break;
        case 'ВП': fieldDisplay = 'ВП'; break;
        case 'ПИ': fieldDisplay = 'Пилот-инструктор'; break;
        
    }
    
    modal.innerHTML = `
        <div style="background:#fff;padding:30px;border-radius:10px;min-width:320px;text-align:center">
            <h4 style="margin-bottom:20px;">Подтверждение замены</h4>
            <p style="color:#666;margin-bottom:20px;">
                На позицию <strong>"${fieldDisplay}"</strong> уже назначен сотрудник:<br>
                <strong>"${currentName}"</strong>
            </p>
            <p style="color:#666;margin-bottom:25px;">Хотите заменить его?</p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button id="replace-yes" style="
                    padding: 8px 20px;
                    background: #28a745;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">ДА</button>
                <button id="replace-no" style="
                    padding: 8px 20px;
                    background: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">НЕТ</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('replace-yes').onclick = function() {
        modal.remove();
        onReplace();
    };
    
    document.getElementById('replace-no').onclick = function() {
        modal.remove();
    };
    
    modal.onclick = e => { if (e.target === modal) modal.remove(); };
}

// --- Модалка предупреждения о требованиях ---
function showRequirementsWarningModal(crew, aircraftTypeIcao, requirements, onContinue) {
    console.log('showRequirementsWarningModal called with:', { crew, aircraftTypeIcao, requirements });
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10002;
    `;
    
    // Текстовое представление статуса
    const getStatusTextRu = (status) => {
        switch (status) {
            case 'expired':
                return 'ИСТЕКЛО';
            case 'warning':
            case 'expiring':
                return 'ИСТЕКАЕТ';
            default:
                return 'СТАТУС НЕИЗВЕСТЕН';
        }
    };
    
    let listHtml = '';
    try {
        listHtml = requirements.map(r => {
            const name = r.name || r.short_name || r.requirement_name || 'Неизвестное требование';
            const expiry = r.expiry_date || r.expiry || r.ValidUntil || r.ExpiryDate || '-';
            const statusText = getStatusTextRu(r.status);
            return `
                <li style="margin-bottom:6px;">
                    <strong>${name}</strong>
                    <div style="font-size:12px;">
                        Статус: <span style="font-weight:bold;">${statusText}</span>
                        ${expiry && expiry !== '-' ? `<br>Срок действия: ${expiry}` : ''}
                    </div>
                </li>
            `;
        }).join('');
    } catch (e) {
        console.error('Error while building requirements list for warning modal:', e);
        listHtml = '<li>Не удалось получить детализированный список требований</li>';
    }
    
    const crewName = crew.ShortName || crew.short_name || crew.fio || crew.name || 'Неизвестный сотрудник';
    const acTypeText = aircraftTypeIcao && aircraftTypeIcao !== '-' ? aircraftTypeIcao : 'данного типа ВС';
    
    modal.innerHTML = `
        <div style="background:#fff;padding:24px 28px;border-radius:10px;min-width:360px;max-width:640px;text-align:left;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
            <h4 style="margin-bottom:14px;color:#dc3545;">Внимание по требованиям</h4>
            <p style="margin-bottom:10px;color:#333;">
                Для сотрудника <strong>${crewName}</strong> по типу ВС <strong>${acTypeText}</strong>
                обнаружены истекшие или истекающие требования:
            </p>
            <ul style="padding-left:18px;margin-bottom:16px;max-height:220px;overflow-y:auto;">
                ${listHtml}
            </ul>
            <p style="margin-bottom:18px;color:#555;">
                Вы уверены, что хотите назначить данного сотрудника на рейс этого типа ВС?
            </p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button id="req-warning-cancel" style="
                    padding: 8px 18px;
                    background: #6c757d;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">Отмена</button>
                <button id="req-warning-continue" style="
                    padding: 8px 18px;
                    background: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">Продолжить назначение</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    document.getElementById('req-warning-cancel').onclick = function() {
        modal.remove();
    };
    
    document.getElementById('req-warning-continue').onclick = function() {
        modal.remove();
        if (onContinue) {
            onContinue();
        }
    };
    
    modal.onclick = function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    };
}

// --- Основная логика назначения ---
function assignCrewToFlight(flight, crew, pos) {
    console.log(flight, "рейс");
    console.log(crew, "Член экипажа");
    console.log(pos, "Позиция");

    let crewName = crew.ShortName || crew.short_name || crew.fio || crew.name || crew.FullName || crew.full_name || crew.id || '???';
    let field = pos;
    let currentName = flight[field];

    console.log(currentName, "currentName");
    console.log(crewName, "crewName");

    if (currentName && currentName !== crewName) {
        showReplaceModal(field, currentName, () => {
            flight[field] = crewName;
            console.log(flight.id, "flight.id");
            console.log(crew.id, "crew.id");
            console.log(field, "field");
            console.log(crewName, "crewName");
            updateCrewAssignmentInDB(flight.id, crew.id, field, crewName);
        });
    } else {
        flight[field] = crewName;
        updateCrewAssignmentInDB(flight.id, crew.id, field, crewName);
    }
}

function updateCrewAssignmentInDB(flightId, crewId, position, crewName) {
    console.log("flightid=", flightId, "crewid=", crewId, "position=", position, "crewName=", crewName);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/planning/assign-crew', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-CSRF-TOKEN': csrfToken, // CSRF-защита
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

// --- Вызов после нажатия ДА в модалке "Добавить рейс для данного сотрудника?" ---
function handleAddCrewToFlight(flight, crew) {
    console.log('handleAddCrewToFlight called with:', { flight, crew });
    showPositionModal(flight, crew, function(pos) {
        console.log('Position selected:', pos);
        assignCrewToFlight(flight, crew, pos);
    });
}

}); // Закрываем DOMContentLoaded