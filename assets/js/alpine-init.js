document.addEventListener('alpine:init', () => {
    Alpine.data('fileScanner', () => ({
        scanProgress: 0,
        isScanning: false,
        totalSize: 0,

        startScan() {
            this.isScanning = true;
            this.scanProgress = 0;

            fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    action: "fsc_scan_files",
                    nonce: fsc_scan_nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.totalSize = data.data.size;
                    this.scanProgress = 100;
                }
                this.isScanning = false;
            })
            .catch(error => {
                console.error("Scan failed:", error);
                this.isScanning = false;
            });
        }
    }));
});
