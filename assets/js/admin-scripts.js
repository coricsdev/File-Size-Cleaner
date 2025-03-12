document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Vanilla JS script loaded.");

    if (!document.getElementById("fileScanner")) {
        console.warn("⚠️ File Cleaner page not detected. Skipping script.");
        return;
    }

    // ✅ Check if fsc_data is available
    if (typeof fsc_data === 'undefined') {
        console.error("❌ fsc_data is NOT defined. Ensure wp_localize_script is correctly loaded.");
        return;
    }
    
    console.log("✅ fsc_data Loaded:", fsc_data);

    const startScanBtn = document.getElementById("startScanBtn");
    const progressBar = document.getElementById("progressBar");
    const progressBarFill = document.getElementById("progressBarFill");
    const totalSizeElement = document.getElementById("totalSize");
    const scanPercentageElement = document.getElementById("scanPercentage");
    let resultsTable = document.getElementById("resultsTable");

    if (!resultsTable) {
        resultsTable = document.createElement("div");
        resultsTable.id = "resultsTable";
        document.getElementById("fileScanner").appendChild(resultsTable);
    }

    let isScanning = false;
    let fileCache = {}; // Store full directory structure in memory

    if (!startScanBtn || !progressBar || !progressBarFill) {
        console.error("❌ Required elements not found in DOM.");
        return;
    }
    // Declare progressInterval at the top
    let progressInterval;

    startScanBtn.addEventListener("click", function () {
        if (isScanning) return;
        isScanning = true;
    
        console.log("✅ Start scan button clicked.");
    
        progressBar.style.display = "block"; // ✅ Ensure Progress Bar is shown
        progressBarFill.style.width = "0%";
        scanPercentageElement.style.display = "block";
        scanPercentageElement.textContent = "0%";
    
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress = Math.min(100, progress + Math.floor(Math.random() * 10) + 5);
            progressBarFill.style.width = progress + "%";
            scanPercentageElement.textContent = progress + "%";
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
    
            console.log("✅ Data Received:", data.data);
    
            if (data.success && data.data) {
                fileCache = data.data.files;
    
                if (totalSizeElement) {
                    totalSizeElement.textContent = formatSize(data.data.totalSize);
                    console.log("📏 Total Size Updated:", formatSize(data.data.totalSize));
                }
    
                displayResults(data.data.files);
            } else {
                console.error("❌ Scan failed or no data received:", data);
                document.getElementById("resultsTable").innerHTML = "<tr><td colspan='6'>No files found.</td></tr>";
            }
    
            isScanning = false;
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error("❌ Error during scan:", error);
            isScanning = false;
        });
    });
    
    


    // 🔥 Display Results Function
    function displayResults(data) {
        console.log("✅ Displaying scan results...");
        
        let resultsTable = document.getElementById("resultsTable");
        if (!resultsTable) {
            console.error("❌ resultsTable is missing from the DOM!");
            return;
        }
    
        if (!Array.isArray(data) || data.length === 0) {
            resultsTable.innerHTML = "<p>No files found.</p>";
            console.warn("⚠️ No files found in scan.");
            return;
        }
    
        resultsTable.innerHTML = `
            <div class="file-cleaner-table-container">
                <table class="file-cleaner-table">
                    <thead>
                        <tr>
                            <th>File/Folder Name</th>
                            <th>Size</th>
                            <th>Location</th>
                            <th>Last Modified</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${generateFileTree(data)}
                    </tbody>
                </table>
            </div>
        `;
    
        // ✅ Ensure folder toggles work after rendering
        document.querySelectorAll(".folder-toggle").forEach(button => {
            button.addEventListener("click", function (event) {
                event.preventDefault();
                toggleFolder(button);
            });
        });
    
        console.log("✅ Table Content Updated:", resultsTable.innerHTML);
    }

    
    let expandedFolders = new Set(); // 🔥 Store opened folders
    
    function toggleFolder(button) {
        let folderId = button.getAttribute("data-folder");
        if (!folderId || !fileCache) return;
    
        let isExpanded = button.getAttribute("aria-expanded") === "true";
    
        if (isExpanded) {
            // 🔥 Close the folder and ALL of its nested subfolders
            collapseAllNestedFolders(folderId);
            button.setAttribute("aria-expanded", "false");
            button.innerHTML = "▶";
            return;
        }
    
        // 🔥 Open only the direct child elements
        let folderPath = folderId.replace(/-/g, "/");
        let parentFolder = findFolderInCache(fileCache, folderPath);
    
        if (parentFolder && Array.isArray(parentFolder.subfiles) && parentFolder.subfiles.length > 0) {
            let rows = generateFileTree(parentFolder.subfiles, folderPath, parseInt(button.closest("tr").getAttribute("data-depth")) + 1);
            let parentRow = button.closest("tr");
    
            // Check if subfolders already exist
            if (!document.querySelector(`.folder-${CSS.escape(folderId)}`)) {
                parentRow.insertAdjacentHTML("afterend", rows);
            }
        }
    
        // 🔥 Show child rows when opening
        document.querySelectorAll(`tr[data-parent="${folderId}"]`).forEach(row => {
            row.style.display = "table-row";
        });
    
        button.setAttribute("aria-expanded", "true");
        button.innerHTML = "▼";
    }
    
    // 🔥 Recursively Collapse ALL Nested Subfolders
    function collapseAllNestedFolders(parentFolderId) {
        let allNestedRows = document.querySelectorAll(`tr[data-parent^="${parentFolderId}"]`);

        allNestedRows.forEach(row => {
            row.style.display = "none";

            let folderToggle = row.querySelector(".folder-toggle");
            if (folderToggle) {
                folderToggle.setAttribute("aria-expanded", "false");
                folderToggle.innerHTML = "▶";
            }
        });
    }

    // 🔥 Search for folder in Cache
    function findFolderInCache(files, path) {
        for (let file of files) {
            if (file.location === path) return file;
            if (Array.isArray(file.subfiles) && file.subfiles.length > 0) {
                let found = findFolderInCache(file.subfiles, path);
                if (found) return found;
            }
        }
        return null;
    }

    // 🔥 Format File Sizes
    function formatSize(size) {
        if (size > 1e9) return (size / 1e9).toFixed(2) + " GB";
        if (size > 1e6) return (size / 1e6).toFixed(2) + " MB";
        if (size > 1e3) return (size / 1e3).toFixed(2) + " KB";
        return size + " bytes";
    }

    function isWordPressCoreFile(filePath) {
        const coreDirectories = [
            "/wp-admin", 
            "/wp-includes"
        ];
    
        const coreFiles = [
            "/index.php",
            "/wp-config.php",
            "/wp-settings.php",
            "/wp-load.php",
            "/wp-cron.php",
            "/xmlrpc.php",
            "/license.txt",
            "/readme.html"
        ];
    
        return coreDirectories.some(dir => filePath.startsWith(dir)) || coreFiles.includes(filePath);
    }
    

    // 🔥 Generate File Tree
    function generateFileTree(files = [], parentFolder = "", depth = 0) {
        if (!Array.isArray(files) || files.length === 0) {
            return "";
        }
    
        let rows = "";
        files.forEach(file => {
            let folderId = file.location.replace(/\//g, "-");
            let isFolder = file.type === "Folder";
            let paddingLeft = 20 + depth * 15;
            let isCoreFile = isWordPressCoreFile(file.location); // Check if file is a WP core file
    
            rows += `
                <tr class="file-row folder-${folderId}" data-depth="${depth}" data-parent="${parentFolder}" style="display: ${depth === 0 ? "table-row" : "none"};">
                    <td style="padding-left: ${paddingLeft}px;">
                        ${isFolder 
                            ? `<span class="folder-toggle" data-folder="${folderId}" aria-expanded="false">▶</span> <span class="folder-name">${file.name}</span>` 
                            : `<span class="file-icon">📄</span> ${file.name}`}
                    </td>
                    <td>${formatSize(file.size)}</td>
                    <td>${file.location}</td>
                    <td>${file.modified ? new Date(file.modified * 1000).toLocaleString() : 'Unknown'}</td>
                    <td>${file.type}</td>
                    <td>
                        ${isCoreFile || !file.path 
                            ? `<span class="disabled-action">🚫 Not Editable</span>` 
                            : `<button class="delete-btn" data-path="${file.path}">❌ Delete</button>`
                        }
                    </td>
                </tr>
            `;
    
            if (isFolder && Array.isArray(file.subfiles) && file.subfiles.length > 0) {
                rows += generateFileTree(file.subfiles, folderId, depth + 1);
            }
        });
    
        return rows;
    }
    
    // Event delegation for delete buttons
    document.addEventListener("click", function (event) {
    if (event.target.classList.contains("delete-btn")) {
        let filePath = event.target.getAttribute("data-path"); // ✅ Correctly fetch file path
        console.log("🛠️ Delete button clicked for:", filePath);

        if (!filePath || filePath.trim() === "") {
            console.error("❌ ERROR: File path is missing from delete button.");
            alert("Error: Cannot delete. File path is missing.");
            return;
        }

        showDeleteConfirmation(filePath, event.target);
    }
});

    

    // Show delete confirmation popup
    function showDeleteConfirmation(filePath, deleteButton) {
        if (!filePath || filePath.trim() === "") {
            console.error("❌ Invalid file path detected.");
            alert("Error: Cannot delete. File path is missing.");
            return;
        }
    
        console.log("🛠️ Showing delete confirmation for:", filePath);
    
        let confirmation = document.createElement("div");
        confirmation.id = "deleteConfirmation";
        confirmation.innerHTML = `
            <div class="delete-popup">
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to permanently delete <strong>${filePath}</strong>? <br>
                <span style="color: red;">This action cannot be undone!</span> <br><br>
                We recommend backing up your files before proceeding.</p>
                <div class="popup-actions">
                    <button id="cancelDelete" class="cancel-btn">Cancel</button>
                    <button id="confirmDelete" class="confirm-btn">Delete Permanently</button>
                </div>
            </div>
        `;
        document.body.appendChild(confirmation);
    
        document.getElementById("cancelDelete").addEventListener("click", function () {
            confirmation.remove();
        });
    
        document.getElementById("confirmDelete").addEventListener("click", function () {
            confirmation.remove();
            deleteFile(filePath, deleteButton);
        });
    }
    
    

    // Perform the actual deletion
    function deleteFile(filePath, deleteButton) {
        if (!filePath) {
            console.error("❌ No file path provided for deletion.");
            alert("Error: No file path provided.");
            return;
        }
    
        console.log("🛠️ Sending Delete Request for:", filePath);
    
        fetch(fsc_data.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "fsc_delete_file",
                nonce: fsc_data.delete_nonce,
                path: filePath
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("✅ File deleted successfully:", filePath);
                let row = deleteButton.closest("tr");
                if (row) row.remove();
            } else {
                console.error("❌ File deletion failed:", data);
                alert("Error: " + (data.message || "Unknown error"));
            }
        })
        .catch(error => {
            console.error("❌ Error deleting file:", error);
            alert("Error: Unable to delete file.");
        });
    }
    
    
    
    
    
    
    
});
