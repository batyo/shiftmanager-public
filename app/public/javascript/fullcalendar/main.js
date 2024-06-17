document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    // リストビューのドットアイコンのスタイルを変更
    document.documentElement.style.setProperty("--fc-list-event-dot-width","0px");
  
    var calendar = new FullCalendar.Calendar(calendarEl, {
      locale: "ja", // 日本語化
      initialView: 'listMonth', // カレンダーの初期表示を月ビューに設定
      events: eventData, // イベントデータ

      validRange: { // カレンダーの表示範囲
        start: new Date().toISOString().split('T')[0], // 今日の日付を開始として設定
        // end : 終了日の設定
      },
      
      eventContent: function(info) {
        /**
         * @var {int} eventId イベントID
         * @var {string} eventTitle title: シフト名
         * @var {string} employeeName describe: マッチング中の従業員名
         */
        var eventId = info.event.id;
        var eventTitle = info.event.title;
        var employeeName = info.event.extendedProps.description[1];

        // label タグの作成
        var labelRadioButton = document.createElement("label");
        labelRadioButton.setAttribute("for", "inputId-" + eventId);
        labelRadioButton.setAttribute("id", "label-radio");

        // radio ボタンの作成
        var radioButton = document.createElement("input");
        radioButton.setAttribute("id", "inputId-" + eventId);
        radioButton.setAttribute("class", "inputId-"+ eventId + " input-radio");
        radioButton.setAttribute("type", "radio");
        radioButton.setAttribute("name", "deleteMatchingId");
        radioButton.setAttribute("value", eventId);

        var shiftName = document.createElement("b");
        shiftName.textContent = eventTitle;

        var assignmentEmployee = document.createElement("text");
        assignmentEmployee.setAttribute("class", "mobile-event-text");
        assignmentEmployee.textContent = " 出勤従業員: " + employeeName;

        var container = document.createElement("div");
        container.setAttribute("class", "div-container");
        
        var labelWrapper = document.createElement("div");
        labelWrapper.setAttribute("class", "div-label-wrapper");

        container.appendChild(labelWrapper);
        labelWrapper.appendChild(labelRadioButton);
        labelRadioButton.appendChild(radioButton);
        labelRadioButton.appendChild(shiftName);
        labelRadioButton.appendChild(assignmentEmployee);

        return { domNodes: [container] }
      },

      eventClick: (e)=>{// イベントのクリックイベント
        console.log("eventClick:", e.event);
      },

      noEventsContent: { html : "<h3>今月のシフト情報はまだありません</h3>" },
      contentHeight: 'auto' // カレンダーの高さを自動調整
    });
  
    calendar.render(); // カレンダーを描画
});
