document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
  
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
         * @var {string} eventTitle title: シフト名
         * @var {int} eventDescription describe: ステータス ("出勤" or "出勤可能")
         */
        var eventTitle = info.event.title;
        var eventDescription = info.event.extendedProps.description;

        // describe 定義済みの場合
        if (eventDescription !== undefined) {
          return { html: '<b>' + eventTitle + '</b> ' + eventDescription};
        }
        // describe 未定義の場合
        if (eventDescription == undefined) {
          return {html: '<b>' + eventTitle + '</b> ステータス不明 (要確認)'};
        }
      },

      noEventsContent: { html : "<h3>今月のシフト情報はまだありません</h3>" },
      contentHeight: 'auto' // カレンダーの高さを自動調整
    });
  
    calendar.render(); // カレンダーを描画
});