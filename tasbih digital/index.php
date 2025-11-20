<?php
include 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$today_count = 0;
$active_session = null;

// Daftar kalimat thayyibah preset
$preset_thayyibah = [
    'Subhanallah' => 'سُبْحَانَ اللَّهِ',
    'Alhamdulillah' => 'الْحَمْدُ لِلَّهِ',
    'Allahuakbar' => 'اللَّهُ أَكْبَرُ',
    'Astaghfirullah' => 'أَسْتَغْفِرُ اللَّهَ',
    'Lailahaillallah' => 'لَا إِلَهَ إِلَّا اللَّهُ'
];

// Ambil custom thayyibah dari session
$custom_thayyibah_list = isset($_SESSION['custom_thayyibah']) ? $_SESSION['custom_thayyibah'] : [];

if($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah ada session aktif
    try {
        $stmt = $pdo->prepare("SELECT id, count, start_time, thayyibah_text FROM tasbih_records WHERE user_id = ? AND end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $active_session = $stmt->fetch();
        
        // Ambil total hari ini
        $stmt = $pdo->prepare("SELECT SUM(count) as total FROM tasbih_records WHERE user_id = ? AND date = CURDATE()");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $today_count = $result['total'] ?: 0;
        
    } catch(PDOException $e) {
        $today_count = 0;
    }
}

// Default kalimat thayyibah
$current_thayyibah = 'Subhanallah';
$current_thayyibah_arabic = $preset_thayyibah[$current_thayyibah];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasbih Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Tasbih Digital</h1>
            <?php if($is_logged_in): ?>
                <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
                <a href="history.php" class="btn-history">Riwayat</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <p>Gunakan tasbih digital secara gratis</p>
                <a href="login.php?redirect=index" class="btn-login">Login</a>
                <a href="register.php" class="btn-register">Daftar</a>
            <?php endif; ?>
        </header>
        
        <?php if($active_session): ?>
        <div class="session-notice">
            <p>📝 Anda memiliki sesi yang belum selesai dari <?php echo date('H:i', strtotime($active_session['start_time'])); ?></p>
            <p>Jumlah: <span id="existingCount"><?php echo $active_session['count']; ?></span> - Kalimat: <?php echo $active_session['thayyibah_text']; ?></p>
            <div class="session-actions-main">
                <button id="continueSessionBtn" class="btn-continue-main" data-session-id="<?php echo $active_session['id']; ?>" data-existing-count="<?php echo $active_session['count']; ?>">
                    Lanjutkan Sesi Ini
                </button>
                <a href="history.php?complete=<?php echo $active_session['id']; ?>" class="btn-complete-main" onclick="return confirm('Selesaikan sesi ini? Data akan disimpan.')">
                    Selesaikan Sesi
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Kalimat Thayyibah -->
        <div class="thayyibah-section">
            <h3>Pilih Kalimat Thayyibah</h3>
            
            <div class="current-thayyibah">
                <div class="label">Kalimat Saat Ini:</div>
                <div class="text" id="currentThayyibahText"><?php echo $current_thayyibah; ?></div>
                <div class="arabic" id="currentThayyibahArabic">
                    <?php 
                    // Cek apakah kalimat termasuk preset (ada tulisan Arab)
                    if (isset($preset_thayyibah[$current_thayyibah])) {
                        echo $preset_thayyibah[$current_thayyibah];
                    } else {
                        // Untuk custom, tampilkan sama dengan latin atau kosong
                        echo $current_thayyibah;
                    }
                    ?>
                </div>
            </div>
            
            <div class="thayyibah-dropdown-wrapper">
                <select id="thayyibahDropdown" class="thayyibah-dropdown">
                    <option value="">Pilih Kalimat Thayyibah...</option>
                    
                    <!-- Preset Thayyibah -->
                    <optgroup label="Kalimat Preset">
                        <?php foreach($preset_thayyibah as $latin => $arabic): ?>
                            <option value="<?php echo $latin; ?>" data-arabic="<?php echo $arabic; ?>" <?php echo $current_thayyibah == $latin ? 'selected' : ''; ?>>
                                <?php echo $latin; ?> (<?php echo $arabic; ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    
                    <!-- Custom Thayyibah -->
                    <?php if(!empty($custom_thayyibah_list)): ?>
                    <optgroup label="Kalimat Custom">
                        <?php foreach($custom_thayyibah_list as $custom): ?>
                            <option value="<?php echo $custom; ?>" data-arabic="<?php echo $custom; ?>" <?php echo $current_thayyibah == $custom ? 'selected' : ''; ?>>
                                <?php echo $custom; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    
                    <option value="custom">-- Ketik Kalimat Baru --</option>
                </select>
            </div>
            
            <div class="custom-thayyibah" id="customThayyibahSection" style="display: none;">
                <div class="custom-input-group">
                    <input type="text" id="customThayyibahInput" placeholder="Ketik kalimat thayyibah baru" maxlength="100">
                    <button class="btn-add-custom" id="addCustomThayyibah">Tambah</button>
                </div>
                
                <?php if(!empty($custom_thayyibah_list)): ?>
                <div class="custom-thayyibah-list">
                    <h4>Kalimat Custom Anda:</h4>
                    <div id="customListContainer">
                        <?php foreach($custom_thayyibah_list as $index => $custom): ?>
                            <div class="custom-thayyibah-item">
                                <span class="custom-thayyibah-text"><?php echo htmlspecialchars($custom); ?></span>
                                <button class="btn-remove-custom" onclick="removeCustomThayyibah('<?php echo $custom; ?>')">
                                    Hapus
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Section Hitung Tasbih -->
        <div class="tasbih-section">
            <div class="tasbih-counter">
                <div class="counter-display" id="counter">0</div>
                <?php if($is_logged_in): ?>
                    <p class="today-count">Hari ini: <?php echo $today_count; ?></p>
                <?php else: ?>
                    <p class="today-count">Login untuk menyimpan riwayat</p>
                <?php endif; ?>
            </div>
            
            <div class="tasbih-area" id="tasbihArea">
                <div class="circle" id="tasbihCircle">
                    <span>TAP</span>
                </div>
            </div>
            
            <div class="actions">
                <button id="resetBtn" class="btn btn-reset">Reset</button>
                
                <?php if($is_logged_in): ?>
                    <button id="saveBtn" class="btn btn-save">Simpan & Selesai</button>
                    <button id="pauseBtn" class="btn btn-pause">Pause</button>
                <?php else: ?>
                    <button id="loginToSaveBtn" class="btn btn-save">Login untuk Simpan</button>
                <?php endif; ?>
            </div>
            
            <div class="quick-buttons">
                <button class="quick-btn" data-increment="10">+10</button>
                <button class="quick-btn" data-increment="33">+33</button>
                <button class="quick-btn" data-increment="100">+100</button>
            </div>
        </div>

        <?php if(!$is_logged_in): ?>
            <div class="guest-notice">
                <p>⚠ Data tidak akan tersimpan tanpa login</p>
            </div>
        <?php endif; ?>
    </div>

    
    <script>
        // Variabel global
        let count = 0;
        let currentThayyibah = '<?php echo $current_thayyibah; ?>';
        let currentThayyibahArabic = '<?php echo $current_thayyibah_arabic; ?>';

        // Fungsi utama yang dijalankan ketika halaman siap
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - initializing...');
            
            initializeTasbih();
            initializeThayyibah();
            initializeSession();
        });

        // ===== INISIALISASI TASBIH =====
        function initializeTasbih() {
            const counterElement = document.getElementById('counter');
            const tasbihCircle = document.getElementById('tasbihCircle');
            const tasbihArea = document.getElementById('tasbihArea');
            const resetBtn = document.getElementById('resetBtn');
            const quickButtons = document.querySelectorAll('.quick-btn');

            // Fungsi update counter
            function updateCounter() {
                if (counterElement) {
                    counterElement.textContent = count;
                    counterElement.classList.add('pulse');
                    setTimeout(() => counterElement.classList.remove('pulse'), 200);
                }
            }

            // Event listeners untuk tasbih
            if (tasbihCircle) {
                tasbihCircle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    count++;
                    updateCounter();
                    this.classList.add('tap-animation');
                    setTimeout(() => this.classList.remove('tap-animation'), 300);
                });
            }

            if (tasbihArea) {
                tasbihArea.addEventListener('click', function(e) {
                    if(e.target === tasbihArea) {
                        count++;
                        updateCounter();
                        const ripple = document.createElement('div');
                        ripple.classList.add('ripple');
                        ripple.style.left = e.clientX + 'px';
                        ripple.style.top = e.clientY + 'px';
                        tasbihArea.appendChild(ripple);
                        setTimeout(() => ripple.remove(), 600);
                    }
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if(count > 0 && confirm('Reset penghitungan?')) {
                        count = 0;
                        updateCounter();
                    }
                });
            }

            quickButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const increment = parseInt(this.getAttribute('data-increment'));
                    count += increment;
                    updateCounter();
                });
            });

            // Tombol save dan pause untuk user login
            <?php if($is_logged_in): ?>
            const saveBtn = document.getElementById('saveBtn');
            const pauseBtn = document.getElementById('pauseBtn');

            if (saveBtn) {
                saveBtn.addEventListener('click', handleSave);
            }

            if (pauseBtn) {
                pauseBtn.addEventListener('click', handlePause);
            }
            <?php else: ?>
            const loginToSaveBtn = document.getElementById('loginToSaveBtn');
            if (loginToSaveBtn) {
                loginToSaveBtn.addEventListener('click', handleLoginToSave);
            }
            <?php endif; ?>
        }

        // ===== INISIALISASI THAYYIBAH =====
        function initializeThayyibah() {
            console.log('Initializing thayyibah...');
            
            const dropdown = document.getElementById('thayyibahDropdown');
            const customSection = document.getElementById('customThayyibahSection');
            const customInput = document.getElementById('customThayyibahInput');
            const addButton = document.getElementById('addCustomThayyibah');

            console.log('Thayyibah elements:', { dropdown, customSection, customInput, addButton });

            // Dropdown change event - SANGAT SEDERHANA
            if (dropdown) {
                dropdown.addEventListener('change', function() {
                    console.log('Dropdown changed to:', this.value);
                    
                    if (this.value === 'custom') {
                        // Tampilkan input custom
                        if (customSection) {
                            customSection.style.display = 'block';
                            // Focus input setelah render
                            setTimeout(() => {
                                if (customInput) {
                                    customInput.focus();
                                    console.log('Input focused');
                                }
                            }, 100);
                        }
                    } else if (this.value) {
                        // Sembunyikan input dan update current
                        if (customSection) {
                            customSection.style.display = 'none';
                        }
                        updateCurrentThayyibah(this.value, this.options[this.selectedIndex].getAttribute('data-arabic'));
                    }
                });
            }

            // Tombol tambah custom - SANGAT SEDERHANA
            if (addButton && customInput) {
                addButton.addEventListener('click', function() {
                    console.log('Add button clicked');
                    addCustomThayyibah();
                });

                // Enter key
                customInput.addEventListener('keypress', function(e) {
                    if(e.key === 'Enter') {
                        e.preventDefault();
                        console.log('Enter pressed');
                        addCustomThayyibah();
                    }
                });

                // Pastikan input bisa menerima input
                customInput.addEventListener('input', function(e) {
                    console.log('Input value:', e.target.value);
                });
            }

            // Fungsi update current thayyibah
            function updateCurrentThayyibah(latin, arabic) {
                currentThayyibah = latin;
                currentThayyibahArabic = arabic;
                const textEl = document.getElementById('currentThayyibahText');
                const arabicEl = document.getElementById('currentThayyibahArabic');
                if (textEl) textEl.textContent = latin;
                if (arabicEl) arabicEl.textContent = arabic;
            }

            // Fungsi tambah custom thayyibah
            function addCustomThayyibah() {
                const input = document.getElementById('customThayyibahInput');
                if (!input) {
                    console.error('Input not found');
                    return;
                }

                const text = input.value.trim();
                console.log('Adding custom thayyibah:', text);

                if (!text) {
                    alert('Silakan ketik kalimat thayyibah terlebih dahulu.');
                    input.focus();
                    return;
                }

                // Simpan ke server
                saveCustomThayyibah(text);
            }
        }

        // ===== FUNGSI BANTUAN =====
        function saveCustomThayyibah(customText) {
            console.log('Saving custom thayyibah:', customText);
            
            fetch('save_custom_thayyibah.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'custom_thayyibah=' + encodeURIComponent(customText)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update current - untuk custom, arabic sama dengan latin
                    updateThayyibahDisplay(customText, customText);
                    
                    // Clear input
                    const input = document.getElementById('customThayyibahInput');
                    if (input) input.value = '';
                    
                    // Hide section
                    const section = document.getElementById('customThayyibahSection');
                    if (section) section.style.display = 'none';
                    
                    // Update dropdown
                    addToDropdown(customText);
                    
                    alert('Kalimat custom "' + customText + '" telah ditambahkan!');
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan');
            });
        }

        function updateThayyibahDisplay(latin, arabic) {
            currentThayyibah = latin;
            currentThayyibahArabic = arabic;
            const textEl = document.getElementById('currentThayyibahText');
            const arabicEl = document.getElementById('currentThayyibahArabic');
            
            if (textEl) {
                textEl.textContent = latin;
                textEl.style.display = 'block';
            }
            
            if (arabicEl) {
                arabicEl.textContent = arabic;
                // Jika arabic sama dengan latin (custom), sembunyikan atau styling berbeda
                if (arabic === latin) {
                    arabicEl.style.display = 'none'; // Sembunyikan untuk custom
                } else {
                    arabicEl.style.display = 'block'; // Tampilkan untuk preset
                }
            }
        }

        function addToDropdown(customText) {
            const dropdown = document.getElementById('thayyibahDropdown');
            if (!dropdown) return;

            // Cari atau buat optgroup custom
            let customGroup = dropdown.querySelector('optgroup[label="Kalimat Custom"]');
            if (!customGroup) {
                customGroup = document.createElement('optgroup');
                customGroup.label = 'Kalimat Custom';
                const customOption = dropdown.querySelector('option[value="custom"]');
                dropdown.insertBefore(customGroup, customOption);
            }

            // Tambahkan option baru
            const newOption = document.createElement('option');
            newOption.value = customText;
            newOption.setAttribute('data-arabic', customText); // Untuk custom, arabic = latin
            newOption.textContent = customText;
            customGroup.appendChild(newOption);

            // Select yang baru ditambahkan
            dropdown.value = customText;
        }

        // ===== FUNGSI SESSION =====
        function initializeSession() {
            const continueBtn = document.getElementById('continueSessionBtn');
            if (continueBtn) {
                continueBtn.addEventListener('click', function() {
                    const sessionId = this.getAttribute('data-session-id');
                    const existingCount = parseInt(this.getAttribute('data-existing-count'));
                    
                    count = existingCount;
                    updateCounter();
                    this.closest('.session-notice').style.display = 'none';
                    alert('Sesi dilanjutkan! Jumlah: ' + existingCount);
                });
            }

            <?php if($active_session): ?>
            // Load session data jika ada
            const existingCount = <?php echo $active_session['count']; ?>;
            count = existingCount;
            updateCounter();
            
            const sessionThayyibah = '<?php echo $active_session['thayyibah_text']; ?>';
            if(sessionThayyibah) {
                updateThayyibahDisplay(sessionThayyibah, sessionThayyibah);
                const dropdown = document.getElementById('thayyibahDropdown');
                if (dropdown) dropdown.value = sessionThayyibah;
            }
            <?php endif; ?>
        }

        // ===== FUNGSI TASBIH =====
        function updateCounter() {
            const counterElement = document.getElementById('counter');
            if (counterElement) {
                counterElement.textContent = count;
                counterElement.classList.add('pulse');
                setTimeout(() => counterElement.classList.remove('pulse'), 200);
            }
        }

        function handleSave() {
            if(count > 0) {
                this.disabled = true;
                this.textContent = 'Menyimpan...';

                const activeSessionId = <?php echo $active_session ? $active_session['id'] : 'null'; ?>;
                let body = 'count=' + count + '&action=complete&thayyibah=' + encodeURIComponent(currentThayyibah);
                
                if(activeSessionId) {
                    body += '&session_id=' + activeSessionId;
                }

                fetch('save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: body
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Data berhasil disimpan!');
                        count = 0;
                        updateCounter();
                        window.location.href = 'history.php';
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan');
                })
                .finally(() => {
                    this.disabled = false;
                    this.textContent = 'Simpan & Selesai';
                });
            } else {
                alert('Tidak ada data untuk disimpan.');
            }
        }

        function handlePause() {
            if(count > 0) {
                this.disabled = true;
                this.textContent = 'Menjeda...';

                const activeSessionId = <?php echo $active_session ? $active_session['id'] : 'null'; ?>;
                let body = 'count=' + count + '&action=pause&thayyibah=' + encodeURIComponent(currentThayyibah);
                
                if(activeSessionId) {
                    body += '&session_id=' + activeSessionId;
                }

                fetch('save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: body
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Sesi dijeda. Anda bisa lanjutkan nanti dari halaman riwayat.');
                        count = 0;
                        updateCounter();
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan');
                })
                .finally(() => {
                    this.disabled = false;
                    this.textContent = 'Pause';
                });
            } else {
                alert('Tidak ada data untuk dijeda.');
            }
        }

        function handleLoginToSave() {
            if(count > 0) {
                if(confirm('Anda memiliki ' + count + ' jumlah tasbih yang belum disimpan. Login untuk menyimpan?')) {
                    localStorage.setItem('tempTasbihCount', count);
                    localStorage.setItem('tempThayyibah', currentThayyibah);
                    window.location.href = 'login.php?redirect=save&count=' + count;
                }
            } else {
                window.location.href = 'login.php?redirect=index';
            }
        }
        function removeCustomThayyibah(text) {
    if (!confirm('Hapus kalimat "' + text + '" ?')) return;

    fetch('delete_custom_thayyibah.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'custom_thayyibah=' + encodeURIComponent(text)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hapus item dari tampilan
            const items = document.querySelectorAll('.custom-thayyibah-item');
            items.forEach(item => {
                if (item.querySelector('.custom-thayyibah-text').textContent === text) {
                    item.remove();
                }
            });

            // Juga hapus dari dropdown
            const dropdown = document.getElementById('thayyibahDropdown');
            const option = dropdown.querySelector(`option[value="${CSS.escape(text)}"]`);
            if (option) option.remove();

            alert('Kalimat custom berhasil dihapus.');
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(() => alert('Terjadi kesalahan jaringan'));
}

    </script>
</body>
</html>