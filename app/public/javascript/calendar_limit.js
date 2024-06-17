var SECOND_CALENDAR = {
  /**
   * 年の`<option>`を作成する
   */
  createYearOptions: function() {
    const now = new Date();
    const nowYear = now.getFullYear();
    const tenYearsAgo = nowYear - 10; // 選択肢に表示する年の範囲

    // 年の表示範囲
    const yearLimit = parseInt(document.getElementById("year").value);

    const select = document.getElementById("untilYear");
    const children = select.children;

    // <option>を全て削除
    while (children.length) {
      children[0].remove();
    }

    for (let i = tenYearsAgo; i <= yearLimit; i++) {
        const option = document.createElement("option");
        option.value = i;
        option.text = i;
        select.appendChild(option);
    }
  },

  /**
  * 月の`<option>`を作成する
  */
  createMonthOptions: function() {
    const untilYear = document.getElementById("untilYear").value;
    const fromYear = document.getElementById("year").value;
  
    const select = document.getElementById("untilMonth");
    const children = select.children;

    // <option>を全て削除
    while (children.length) {
      children[0].remove();
    }

    // 月の表示範囲を決定する
    let monthLimit = 12;
    if (untilYear == fromYear) {
      monthLimit = parseInt(document.getElementById("month").value);
    }
  
    for (let i = 1; i <= monthLimit; i++) {
      const option = document.createElement("option");
      if (i <= 9) option.value= "0"+i;
      if (i >= 10) option.value= i;
      option.text = i;
      select.appendChild(option);
    }
  },

  /**
  * 選択された年月の末日を取得する
  * 
  * @param {int} year 年
  * @param {int} month 月
  * @returns {int} 年月の末日
  */
  getLastDay: function(year, month) {
    const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    const isLeapYear = (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
    const isFebruary = month === 2;

    if ( !isFebruary ) return daysInMonth[month - 1]; // 2月ではない場合
    if (isLeapYear) return 29; // 閏年で2月の場合
    return daysInMonth[month - 1]; // 閏年でない2月の場合
  },

  /**
  * 日の`<option>`を作成する
  */
  createDayOptions :function() {
    const untilYear = document.getElementById("untilYear").value;
    const untilMonth = document.getElementById("untilMonth").value;
    const select = document.getElementById("untilDay");
    const children = select.children;

    // <option>を全て削除
    while (children.length) {
      children[0].remove();
    }

    const fromYear = document.getElementById("year").value;
    const fromMonth = document.getElementById("month").value;
    const fromDay = parseInt(document.getElementById("day").value);

    const sameYear = fromYear == untilYear;
    const sameMonth = fromMonth == untilMonth;

    // 日の表示範囲を決定する
    let lastDay = SECOND_CALENDAR.getLastDay(parseInt(untilYear), parseInt(untilMonth));
    if (sameYear && sameMonth) lastDay = fromDay;

    // 年と月が選択されている場合
    if (untilYear && untilMonth) {
      for (let i = 1; i <= lastDay; i++) {
          const option = document.createElement("option");
          if (i <= 9) option.value = "0"+i;
          if (i >= 10) option.value = i;
          option.text = i;
          select.appendChild(option);
      }
    }
  },

  /**
   * カレンダー (年月日) を作成
   */
  createCalendar: function() {
    SECOND_CALENDAR.createYearOptions();
    SECOND_CALENDAR.createMonthOptions();
    SECOND_CALENDAR.createDayOptions();
  }
};

// 年月が変更された場合に日のoptionを再取得
document.getElementById("year").addEventListener("change", function(){SECOND_CALENDAR.createCalendar();});
document.getElementById("month").addEventListener("change", function(){SECOND_CALENDAR.createCalendar();});
document.getElementById("day").addEventListener("change", function(){SECOND_CALENDAR.createCalendar();});
document.getElementById("untilYear").addEventListener("change", function(){SECOND_CALENDAR.createMonthOptions();});
document.getElementById("untilMonth").addEventListener("change", function(){SECOND_CALENDAR.createDayOptions();});

SECOND_CALENDAR.createYearOptions();
SECOND_CALENDAR.createMonthOptions();
SECOND_CALENDAR.createDayOptions();
