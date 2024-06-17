document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
  
    var calendar = new FullCalendar.Calendar(calendarEl, {
      locale: "ja", // 日本語化
      initialView: 'listMonth', // カレンダーの初期表示を月ビューに設定

      validRange: { // カレンダーの表示範囲
        start: new Date().toISOString().split('T')[0], // 今日の日付を開始として設定
        // end : 終了日の設定
      },

      events: eventData, // イベントデータ
      eventContent: function(info) {

        const AVAILABLE_SLOTS  = 0;
        const ASSIGNMENT_COUNT = 1;

        /**
         * @var {int} eventId id: イベントID
         * @var {string} eventTitle title: シフト名
         * @var {int} availableSlots description: シフトの空き枠数
         * @var {int} assignmentCount description: 現在のシフト割り当て数
         */
        var eventId = info.event.id;
        var eventTitle = info.event.title;
        var availableSlots = info.event.extendedProps.description[AVAILABLE_SLOTS];
        var assignmentCount = info.event.extendedProps.description[ASSIGNMENT_COUNT];
        

        var labelRadioButton = document.createElement("label");
        labelRadioButton.setAttribute("for", "inputId-" + eventId);
        labelRadioButton.setAttribute("id", "label-radio");

        var radioButton = document.createElement('input');
        radioButton.setAttribute("id", "inputId-" + eventId);
        radioButton.setAttribute("class", "inputId-"+ eventId + " input-radio");
        radioButton.setAttribute("type", "radio");
        radioButton.setAttribute("name", "deleteShiftId");
        radioButton.setAttribute("value", eventId);

        var eventText = document.createElement("text");
        var eventText2 = document.createElement("text");
        eventText.setAttribute("class", "event-text");
        eventText2.setAttribute("class", "event-text");
        eventText.textContent = " 空き枠数: " + availableSlots;
        eventText2.textContent = " 現在の割り当て数: " + assignmentCount;

        var container = document.createElement("div");
        container.setAttribute("class", "div-container");
        
        var labelWrapper = document.createElement("div");
        labelWrapper.setAttribute("class", "div-label-wrapper");
        

        container.appendChild(labelWrapper);
        labelWrapper.appendChild(labelRadioButton);
        // シフトの割り当ては行われている場合は input は生成しない
        if (assignmentCount == 0)  labelRadioButton.appendChild(radioButton);
        labelRadioButton.appendChild(document.createTextNode(eventTitle));
        labelRadioButton.appendChild(eventText);
        labelRadioButton.appendChild(eventText2);
      
        return { domNodes: [container] };
      },

      eventClick: function(info) {
        
        if (info.jsEvent.target.tagName.toLowerCase() === "input") {
          
          // ハイライト表示されているイベントのハイライトを消す
          var highlightedEvent = document.querySelector(".highlight-event");
          if (highlightedEvent !== null) highlightedEvent.classList.remove("highlight-event");

          // クリックしたイベントをハイライト表示する
          info.el.classList.add("highlight-event");
        }
      },

      noEventsContent: { html : "<h3>今月のシフト情報はまだありません</h3>" },
      contentHeight: 'auto' // カレンダーの高さを自動調整
    });
  
    calendar.render(); // カレンダーを描画
});