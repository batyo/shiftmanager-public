document.getElementById("calendar-shifts").addEventListener("submit", function(event) {
    let checkboxes = document.querySelectorAll(".input-checkbox");

    // NodeList から Array を作成
    let checkboxesToArray = Array.from(checkboxes);

    // checkbox が 1 つでもチェックされているか確認
    const isChecked = (checkbox) => checkbox.checked;
    let isExistChecked = checkboxesToArray.some(isChecked);
    
    // checkbox が 1 つもチェックされていない場合
    if ( !isExistChecked ) {
      // submit を中止
      event.preventDefault();
      alert("シフトを選択して下さい。");
    }
});
