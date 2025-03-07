document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Vanilla JS script loaded.");

    // Ensure script only runs on the File Cleaner admin page
    if (!document.getElementById("fileScanner")) {
        console.warn("⚠️ File Cleaner page not detected. Skipping script.");
        return;
    }

    const startScanBtn = document.getElementById("startScanBtn");
    const progressBar = document.getElementById("progressBar");
    const progressBarFill = document.getElementById("progressBarFill");
    const totalSizeElement = document.getElementById("totalSize");
    const scanPercentageElement = document.getElementById("scanPercentage");
    let infoBoxes = document.getElementById("infoBoxes");
    // Ensure `resultsTable` exists inside `fileScanner`
    let resultsTable = document.getElementById("resultsTable");

    if (!resultsTable) {
        console.warn("⚠️ resultsTable not found! Creating it...");
        
        resultsTable = document.createElement("div");
        resultsTable.id = "resultsTable";
    
        const fileScanner = document.getElementById("fileScanner");
        if (!fileScanner) {
            console.error("❌ fileScanner container is missing! Cannot append resultsTable.");
            return;
        }
    
        fileScanner.appendChild(resultsTable);
        console.log("✅ resultsTable successfully appended to fileScanner.");
    } else {
        console.log("✅ resultsTable already exists.");
    }
    


    let isScanning = false;

    if (!startScanBtn || !progressBar || !progressBarFill) {
        console.error("❌ Required elements not found in DOM.");
        return;
    }

    startScanBtn.addEventListener("click", function () {
        if (isScanning) return;
        isScanning = true;

        console.log("✅ Start scan button clicked.");

        // Show progress bar & reset values
        progressBar.style.display = "block";
        progressBarFill.style.width = "0%";
        scanPercentageElement.style.display = "block"; // Show percentage only when scanning
        scanPercentageElement.textContent = "0%";

        let progress = 0;
        const progressInterval = setInterval(() => {
            if (progress < 95) {
                progress += Math.floor(Math.random() * 10) + 5;
                progressBarFill.style.width = progress + "%";
                scanPercentageElement.textContent = progress + "%";
            }
        }, 500);

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
            clearInterval(progressInterval);
            progressBarFill.style.width = "100%";
            scanPercentageElement.textContent = "100%";

            console.log("🔍 Raw AJAX Response:", data); // Debug the entire response

            if (data.success && data.data) {
                console.log("✅ Data Received:", data.data);
                console.log("📂 Files in response:", data.data.files);
            
                // 🔥 Check if totalSizeElement exists before updating
                if (totalSizeElement) {
                    totalSizeElement.textContent = formatSize(data.data.totalSize); // 🔥 Convert bytes to readable size
                } else {
                    console.error("❌ totalSizeElement is missing from the DOM!");
                }
                
            
                if (!data.data.files || data.data.files.length === 0) {
                    console.warn("⚠️ No files found in scan results.");
                } else {
                    displayResults(data.data);
                }
            } else {
                console.error("❌ Scan failed or no data received:", data);
            }            

            isScanning = false;
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error("❌ Error during scan:", error);
        
            // 🔥 Debug: Log which element is causing the issue
            console.log("🔍 Checking elements before modifying...");
            console.log("totalSizeElement:", totalSizeElement);
            console.log("scanPercentageElement:", scanPercentageElement);
        
            isScanning = false;
        });
        
    });

    
    function displayResults(data) {
        console.log("✅ Displaying scan results...");
    
        // 🔥 Always re-fetch resultsTable from the DOM
        let resultsTable = document.getElementById("resultsTable");
    
        if (!resultsTable) {
            console.error("❌ resultsTable is STILL missing from the DOM!");
            return;
        }
    
        console.log("📂 Files in response:", data.files);
    
        // 🔥 Force visibility (in case styles are hiding it)
        resultsTable.style.display = "block";
    
        resultsTable.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>📁 File/Folder Name</th>
                        <th>📏 Size</th>
                        <th>📍 Location</th>
                        <th>📅 Last Modified</th>
                        <th>📂 Type</th>
                        <th>⚡ Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.files.map(file => `
                        <tr>
                            <td>${file.name}</td>
                            <td>${formatSize(file.size)}</td>
                            <td>${file.location}</td>
                            <td>${file.modified ? new Date(file.modified * 1000).toLocaleString() : 'Unknown'}</td> <!-- 🔥 Convert timestamp -->
                            <td>${file.type}</td>
                            <td>
                                ${file.deletable 
                                    ? `<button class="delete-btn" data-path="${file.path}">❌ Delete</button>` 
                                    : `<span class="disabled-action">🚫 Not Editable</span>`}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        console.log("📋 Table Content Updated:", resultsTable.innerHTML);
    }
    
    

    function formatSize(size) {
        if (size > 1e9) return (size / 1e9).toFixed(2) + " GB";
        if (size > 1e6) return (size / 1e6).toFixed(2) + " MB";
        if (size > 1e3) return (size / 1e3).toFixed(2) + " KB";
        return size + " bytes";
    }
    
});
