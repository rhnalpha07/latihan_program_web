<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "localhost";
$user = "root";
$password = "rehan7123"; // If you have set a password, add it here
$dbname = "db_form";

try {
    // Create connection
    $conn = new mysqli($host, $user, $password);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Select the database
    $conn->select_db($dbname);

    // Create table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS kontak (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        pesan TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Get all records from kontak table
    $sql = "SELECT id, nama, email, pesan, created_at FROM kontak ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error fetching data: " . $conn->error);
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Data</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            backdrop-filter: blur(5px);
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table th {
            background: rgba(255, 255, 255, 0.15);
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .data-table tr:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(5px);
            border: 1px solid var(--glass-border);
            display: none;
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .error-message {
            background: rgba(255, 68, 68, 0.2);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        .data-table td:nth-child(3) { /* Email column */
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .data-table td:nth-child(4) { /* Message column */
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .delete-btn {
            padding: 8px 12px;
            background: rgba(255, 68, 68, 0.2);
            color: white;
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            position: relative;
            overflow: hidden;
            transform: scale(1);
        }

        .delete-btn:hover {
            background: rgba(255, 68, 68, 0.3);
            transform: translateY(-2px);
        }

        .delete-btn:active {
            transform: scale(0.95);
        }

        .delete-btn .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .delete-btn.clicked {
            animation: buttonClick 0.4s ease;
        }

        @keyframes buttonClick {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
            }
        }

        .delete-btn i {
            margin-right: 5px;
            transition: transform 0.3s ease;
        }

        .delete-btn:hover i {
            transform: rotate(-10deg);
        }

        .delete-btn.clicked i {
            animation: trashShake 0.4s ease;
        }

        @keyframes trashShake {
            0%, 100% { transform: rotate(0); }
            25% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
        }

        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        #notification.success {
            background: rgba(0, 200, 81, 0.2);
            border: 1px solid rgba(0, 200, 81, 0.3);
        }

        #notification.error {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        #notification.show {
            transform: translateX(0);
        }

        .menu {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .menu-button {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .menu-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .menu-button.active {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
        }

        /* Delete Animation Styles */
        .data-table tr {
            transition: all 0.5s ease;
            position: relative;
            transform-origin: left;
        }

        .data-table tr.deleting {
            animation: deleteRow 0.5s ease forwards;
        }

        @keyframes deleteRow {
            0% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            20% {
                transform: translateX(20px) scale(1.02);
            }
            100% {
                opacity: 0;
                transform: translateX(-100%) scale(0.5);
            }
        }

        /* Delete Particles Effect */
        .particle {
            position: fixed;
            background: rgba(255, 68, 68, 0.3);
            border-radius: 50%;
            pointer-events: none;
            z-index: 1000;
        }

        @keyframes particleAnimation {
            0% {
                opacity: 1;
                transform: translate(0, 0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(var(--tx), var(--ty)) scale(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu glass-effect">
            <a href="index.html" class="menu-button">
                <i class="fas fa-paper-plane"></i> Form Kontak
            </a>
            <a href="view_data.php" class="menu-button active">
                <i class="fas fa-table"></i> Lihat Data
            </a>
        </div>

        <div id="notification"></div>

        <h2><i class="fas fa-table"></i> Data Kontak</h2>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-container glass-effect">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Pesan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = $result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td title="<?php echo htmlspecialchars($row['email']); ?>">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </td>
                                    <td title="<?php echo htmlspecialchars($row['pesan']); ?>">
                                        <?php echo htmlspecialchars($row['pesan']); ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <button class="delete-btn" onclick="deleteData(<?php echo $row['id']; ?>, event)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state" style="display: block;">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada data kontak yang tersimpan.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($conn)) $conn->close(); ?>
    </div>

    <script>
    function createParticles(x, y) {
        const particleCount = 10;
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Random size between 5 and 15 pixels
            const size = Math.random() * 10 + 5;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            // Position at click point
            particle.style.left = `${x}px`;
            particle.style.top = `${y}px`;
            
            // Random direction
            const angle = (Math.random() * 360) * (Math.PI / 180);
            const distance = Math.random() * 100 + 50;
            const tx = Math.cos(angle) * distance;
            const ty = Math.sin(angle) * distance;
            
            // Set custom properties for the animation
            particle.style.setProperty('--tx', `${tx}px`);
            particle.style.setProperty('--ty', `${ty}px`);
            
            // Add animation
            particle.style.animation = 'particleAnimation 0.5s ease-out forwards';
            
            document.body.appendChild(particle);
            
            // Remove particle after animation
            setTimeout(() => {
                particle.remove();
            }, 500);
        }
    }

    function showNotification(message, type) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = type;
        notification.classList.add('show');

        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    function createRipple(event) {
        const button = event.currentTarget;
        const ripple = document.createElement('span');
        
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        
        const rect = button.getBoundingClientRect();
        
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = `${diameter}px`;
        ripple.style.left = `${event.clientX - rect.left - radius}px`;
        ripple.style.top = `${event.clientY - rect.top - radius}px`;
        
        // Remove existing ripples
        const existingRipple = button.getElementsByClassName('ripple')[0];
        if (existingRipple) {
            existingRipple.remove();
        }
        
        button.appendChild(ripple);
        
        // Add clicked class for button animation
        button.classList.add('clicked');
        
        // Remove clicked class after animation
        setTimeout(() => {
            button.classList.remove('clicked');
            ripple.remove();
        }, 600);
    }

    function deleteData(id, event) {
        // Add ripple effect first
        createRipple(event);

        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            // Get the click coordinates for particle effect
            const rect = event.target.getBoundingClientRect();
            const x = event.clientX || rect.left + rect.width / 2;
            const y = event.clientY || rect.top + rect.height / 2;

            const formData = new FormData();
            formData.append('id', id);

            // Get the row before starting the animation
            const row = event.target.closest('tr');
            
            // Add deleting class to start the animation
            row.classList.add('deleting');
            
            // Create particle effect
            createParticles(x, y);

            fetch('delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Wait for animation to finish before removing the row
                    setTimeout(() => {
                        row.remove();

                        // Show success notification
                        showNotification(result.message, 'success');

                        // Check if table is empty
                        const tbody = document.querySelector('.data-table tbody');
                        if (tbody.children.length === 0) {
                            document.querySelector('.table-container').style.display = 'none';
                            document.querySelector('.empty-state').style.display = 'block';
                        }
                    }, 500);
                } else {
                    // Remove the deleting class if there was an error
                    row.classList.remove('deleting');
                    showNotification(result.message, 'error');
                }
            })
            .catch(error => {
                // Remove the deleting class if there was an error
                row.classList.remove('deleting');
                showNotification('Terjadi kesalahan saat menghapus data', 'error');
                console.error('Error:', error);
            });
        }
    }
    </script>
</body>
</html> 