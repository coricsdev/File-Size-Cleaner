document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Vanilla JS script loaded.");

    const startScanBtn = document.getElementById("startScanBtn");
    const progressBar = document.getElementById("progressBar");
    const progressBarFill = document.getElementById("progressBarFill");
    const totalSizeElement = document.getElementById("totalSize");

    let isScanning = false;

    startScanBtn.addEventListener("click", function () {
        if (isScanning) return;
        isScanning = true;

        console.log("✅ Start scan button clicked.");

        // Show progress bar
        progressBar.style.display = "block";
        progressBarFill.style.width = "0%";
        
        fetch(window.ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "fsc_scan_files",
                nonce: window.fsc_scan_nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                totalSizeElement.textContent = data.data.size;
                progressBarFill.style.width = "100%";
                console.log("✅ Scan completed. Total size:", data.data.size);
            } else {
                console.error("❌ Scan failed:", data);
            }
            isScanning = false;
        })
        .catch(error => {
            console.error("❌ Error during scan:", error);
            isScanning = false;
        });
    });
});
