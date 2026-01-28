// script.js

let selectedItemId = null;
let selectedAmount = 1;

/**
 * 商品クリック時の処理 (モーダル表示)
 * @param {number} itemId 商品ID
 * @param {string} itemName 商品名
 */
function confirmOrder(itemId, itemName) {
    selectedItemId = itemId;
    selectedAmount = 1; // Reset amount
    updateQuantityUI();
    document.getElementById('modalItemName').textContent = itemName;
    document.getElementById('orderModal').style.display = 'flex';
}

/**
 * 個数選択
 * @param {number} amount 
 */
function selectQuantity(amount) {
    selectedAmount = amount;
    updateQuantityUI();
}

/**
 * 個数選択UI更新
 */
function updateQuantityUI() {
    const buttons = document.querySelectorAll('.btn-qty');
    buttons.forEach(btn => {
        if (parseInt(btn.textContent) === selectedAmount) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });
}

/**
 * モーダルを閉じる
 */
function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
    selectedItemId = null;
}

/**
 * 注文実行 (モーダルから呼ばれる)
 */
function executeOrder() {
    if (!selectedItemId) return;
    placeOrder(selectedItemId, selectedAmount);
    closeModal();
}

/**
 * 注文処理 (Ajax)
 * @param {number} itemId 
 * @param {number} amount
 */
function placeOrder(itemId, amount) {
    // URLパラメータからテーブル番号を取得
    const urlParams = new URLSearchParams(window.location.search);
    const tableNo = urlParams.get('tableNo') || 1; // デフォルトは1

    // フォームデータ作成
    const formData = new FormData();
    formData.append('itemId', itemId);
    formData.append('tableNo', tableNo);
    formData.append('amount', amount);

    // fetch APIで送信
    fetch('logic.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("注文を受け付けました！");
                // 合計金額を更新 (ページリロードまたはDOM更新)
                // ここではシンプルにリロードして最新状態を反映させる
                location.reload();
            } else {
                alert("注文に失敗しました: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("通信エラーが発生しました");
        });
}
