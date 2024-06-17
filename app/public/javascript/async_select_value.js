/**
 * セレクタタグで選択された値から非同期で希望シフトを取得する
 * 
 * @param {*} selected select.value セレクトタグで選択された値
 */
async function fetchData(selected) {
  try {
    // データを取得するための非同期処理（ここでは例としてfetchを使う）
    const response = await fetch("asyncResponse.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ selected }),
    });
    
    const result = await response.json();

    var eventData = result["eventData"];
    
    // ./async_select_value.js
    writeCalendar(eventData);
    
  } catch (error) {
    // エラーハンドリング
    console.error("Error fetching data:", error);
  }
}

// <select>要素を取得する
const selectElement = document.getElementById("selectEmployee");

// <select>要素の変更を監視し、変更があった場合に値を取得して非同期処理を実行する
selectElement.addEventListener("change", function(event) {
  const selectedOption = event.target.value; // 選択されたオプションの値を取得
  fetchData(selectedOption); // データを取得する非同期関数を呼び出す
});
