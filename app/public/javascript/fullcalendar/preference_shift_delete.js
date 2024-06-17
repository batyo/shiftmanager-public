document.addEventListener('DOMContentLoaded', function() {

    var calendarEl = document.getElementById("calendar");

    // hover をなくす
    document.documentElement.style.setProperty("--fc-list-event-hover-bg-color","none");
  
    // FullCalendar クラス
    var calendar = new FullCalendar.Calendar(calendarEl, {
      locale: "ja", // 日本語化
      initialView: "listMonth", // カレンダーの初期表示を月ビューに設定
      events: eventData, // イベントデータ

      validRange: { // カレンダーの表示範囲
        start: new Date().toISOString().split('T')[0], // 今日の日付を開始として設定
        // end : 終了日の設定
      },

      // HTML要素
      eventContent: function(info) {

        var labelCheckbox = document.createElement("label");
        labelCheckbox.setAttribute("for", "inputId-" + info.event.id);
        labelCheckbox.setAttribute("id", "label-checkbox");

        var checkBox = document.createElement('input');
        checkBox.setAttribute("id", "inputId-"+info.event.id);
        checkBox.setAttribute("class", "inputId-"+ info.event.id + " input-checkbox");
        checkBox.setAttribute("type", "checkbox");
        checkBox.setAttribute("name", "preference[]");
        checkBox.setAttribute("value", info.event.id);
      
        var checkboxInfo = document.createElement("text");
        checkboxInfo.textContent = "希望提出をキャンセルする";

        var eventDescription = document.createElement("p");

        var somethigData = info.event.extendedProps.description[0];

        if (info.event.extendedProps.description !== undefined) {
          eventDescription.textContent = somethigData;
        }

        var container = document.createElement("div");
        container.setAttribute("class", "div-container");
        
        var labelWrapper = document.createElement("div");
        labelWrapper.setAttribute("class", "div-label-wrapper");
        

        container.appendChild(labelWrapper);
        labelWrapper.appendChild(labelCheckbox);
        labelCheckbox.appendChild(document.createTextNode(info.event.title));
        labelCheckbox.appendChild(checkBox);
        labelCheckbox.appendChild(checkboxInfo);
        labelCheckbox.appendChild(eventDescription);
      
        return { domNodes: [container] };
      },
      
      // クリックイベント
      eventClick: function(info) {

        // input タグをクリックした場合
        if (info.jsEvent.target.tagName.toLowerCase() === "input") {
          
          /** 選択したイベントの全ての checkbox を切り替えて親要素 <tr> をハイライト表示する  */

          // id と class には同じ名前を設定している
          var idName = info.jsEvent.target.id;
          var sameClassCheckboxes = document.querySelectorAll('.' + idName);

          // 選択したイベントの checkbox を切り替える
          sameClassCheckboxes.forEach(function(checkBox) {
            /** @var {bool} isChecked checkboxの状態 */
            var isChecked = info.jsEvent.target.checked;
            if (isChecked) checkBox.checked = true; // チェックする
            if (!isChecked) checkBox.checked = false; // チェックを外す

            // 親要素の <tr> をハイライト表示する
            var parentElement = checkBox.parentNode;
            while (parentElement.tagName !== "TR") {
              parentElement = parentElement.parentNode;
            }
            parentElement.classList.toggle("highlight-event"); // ハイライト表示
          });
        }
      },

      noEventsContent: { html : "<h3>今月のシフト情報はまだありません</h3>" },
      contentHeight: "auto", // カレンダーの高さを自動調整
    });
  
    calendar.render(); // カレンダーを描画
});
