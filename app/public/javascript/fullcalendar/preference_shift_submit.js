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
        checkboxInfo.textContent = "希望提出";

        var eventDescription = document.createElement("p");

        var submitStatus = info.event.extendedProps.description[0];

        var isRemovedCheckbox = false; // チェックボックス削除の要否

        if (submitStatus !== undefined) {
          eventDescription.textContent = submitStatus;
          
          // 提出ステータス一覧
          const descriptionString = ["現在提出中", "出勤予定", "「現在提出中」または「出勤予定」のシフトと時間が重なっています"];

          // 提出ステータスのあるフォームは checkbox を削除する
          if (descriptionString.includes(submitStatus)) {
            checkBox.remove();
            isRemovedCheckbox = true;
          }
        }

        var container = document.createElement("div");
        container.setAttribute("class", "div-container");
        
        var labelWrapper = document.createElement("div");
        labelWrapper.setAttribute("class", "div-label-wrapper");
        

        container.appendChild(labelWrapper);
        labelWrapper.appendChild(labelCheckbox);
        labelCheckbox.appendChild(document.createTextNode(info.event.title));
        if ( !isRemovedCheckbox ) {
          labelCheckbox.appendChild(checkBox);
          labelCheckbox.appendChild(checkboxInfo);
        }
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

          /** 時間が被るイベントの checkbox を選択不可にする */

          // 選択したイベント情報
          var eventId_a = info.event.id; // ID
          var startTime_a = info.event.start; // 開始時間
          var endTime_a = info.event.end; // 終了時間

          // 全てのイベント情報を取得し選択したイベント情報と比較する
          calendar.getEvents().forEach(function(event) {

            if (eventId_a == event.id) return; // 選択したイベントはスキップ

            var startTime_b = event.start; // 比較イベントの開始時間
            var endTime_b = event.end; // 比較イベントの終了時間

            /**　@var {bool} isTimeOverlap イベントA と イベントBの時間が被っている場合 true　*/
            var isTimeOverlap = startTime_a < endTime_b && startTime_b < endTime_a;

            // 時間が被っている全てのイベントの checkbox を選択できなくする
            if (isTimeOverlap) {
              let checkboxes = document.querySelectorAll('input[id="inputId-' + event.id + '"]');
              console.log(checkboxes);
              checkboxes.forEach(function(checkbox) {
                let isDisabled = checkbox.disabled; 
                if (isDisabled === true) checkbox.disabled = false;
                if (isDisabled === false) checkbox.disabled = true;
              })
            }
          });
        }
      },

      noEventsContent: { html : "<h3>今月のシフト情報はまだありません</h3>" },
      contentHeight: "auto", // カレンダーの高さを自動調整
    });
  
    calendar.render(); // カレンダーを描画
});
