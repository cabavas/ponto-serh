if(window.indexedDB) {
    var request = indexedDB.open("ponto_serh", 1);

    request.onerror = function(event) {
        console.log("Erro ao abrir o banco de dados");
    };

    request.onupgradeneeded = function(event) {
        var db = event.target.result;
        var objectStore = db.createObjectStore("ponto_serh", { keyGenerator: true });
        objectStore.createIndex("id", "id", { unique: true });
        objectStore.createIndex("entry", "entry", { unique: false });
        objectStore.createIndex("is_user", "id_user", { unique: false });
        objectStore.transaction.oncomplete = function(event) {
            var store = db.transaction("ponto_serh", "readwrite").objectStore("ponto_serh");
        };
    };

    request.onsuccess = function(event) {
        console.log("Banco de dados aberto com sucesso");
    };
}