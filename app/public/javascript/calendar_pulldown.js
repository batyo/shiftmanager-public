/**
 * 年の`<option>`を作成する
 */
function createYearOptions() {
    const now = new Date();
    const nowYear = now.getFullYear();
    const tenYearsAgo = nowYear - 10; // 選択肢に表示する年の範囲

    const select = document.getElementById("year");
    for (let i = tenYearsAgo; i <= nowYear; i++) {
        const option = document.createElement("option");
        option.value = i;
        option.text = i;
        if (i == nowYear) option.selected = true;
        select.appendChild(option);
    }
}

/**
 * 月の`<option>`を作成する
 */
function createMonthOptions() {
    const now = new Date();
    const nowMonth = now.getMonth();

    const select = document.getElementById("month");
    for (let i = 1; i <= 12; i++) {
        const option = document.createElement("option");
        if (i <= 9) option.value= "0"+i;
        if (i >= 10) option.value= i;
        option.text = i;
        if (i == nowMonth) option.selected = true;
        select.appendChild(option);
    }
}

/**
 * 選択された年月の末日を取得する
 * 
 * @param {int} year 年
 * @param {int} month 月
 * @returns {int} 年月の末日
 */
function getLastDay(year, month) {
    const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    const isLeapYear = (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
    const isFebruary = month === 2;

    if ( !isFebruary ) return daysInMonth[month - 1]; // 2月ではない場合
    if (isLeapYear) return 29; // 閏年で2月の場合
    return daysInMonth[month - 1]; // 閏年でない2月の場合
}

/**
 * 日の`<option>`を作成する
 */
function createDayOptions() {
    const now = new Date();
    const nowDate = now.getDate();

    const year = document.getElementById("year").value;
    const month = document.getElementById("month").value;
    const select = document.getElementById("day");
    const children = select.children;

    // <option>を全て削除
    while (children.length) {
        children[0].remove();
    }

    // 年と月が選択されている場合
    if (year && month) {
        const lastDay = getLastDay(parseInt(year), parseInt(month));
        for (let i = 1; i <= lastDay; i++) {
            const option = document.createElement("option");
            if (i <= 9) option.value = "0"+i;
            if (i >= 10) option.value = i;
            option.text = i;
            if (i == nowDate) option.selected = true;
            select.appendChild(option);
        }
    }
}

// 年月が変更された場合に日のoptionを再取得
document.getElementById("year").addEventListener("change", function(){createDayOptions();});
document.getElementById("month").addEventListener("change", function(){createDayOptions();});

createYearOptions();
createMonthOptions();
createDayOptions();
