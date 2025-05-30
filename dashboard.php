<?php
require_once 'config.php';

// Kiểm tra session và quyền truy cập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set active page for sidebar
$activePage = 'dashboard';
$pageTitle = 'Quản lý khách hàng';

if (isset($_GET['logout'])) {
    try {
    $user = new User();
    $user->logout();
    header('Location: login.php');
    exit();
    } catch (Exception $e) {
        $error = "Lỗi đăng xuất: " . htmlspecialchars($e->getMessage());
    }
}

// Lấy danh sách khách hàng và thống kê
try {
    $customer = new Customer();
    $filters = [];
    
    // Validate and sanitize filters
    if (isset($_GET['search'])) {
        $filters['search'] = htmlspecialchars($_GET['search']);
    }
    
    if (isset($_GET['month_birthday'])) {
        $monthBirthday = filter_var($_GET['month_birthday'], FILTER_VALIDATE_INT);
        if ($monthBirthday !== false && $monthBirthday >= 1 && $monthBirthday <= 12) {
            $filters['month_birthday'] = $monthBirthday;
        }
    }
    
    if (isset($_GET['min_purchases'])) {
        $minPurchases = filter_var($_GET['min_purchases'], FILTER_VALIDATE_INT);
        if ($minPurchases !== false && $minPurchases >= 0) {
            $filters['min_purchases'] = $minPurchases;
        }
    }
    
    if (isset($_GET['age_min'])) {
        $ageMin = filter_var($_GET['age_min'], FILTER_VALIDATE_INT);
        if ($ageMin !== false && $ageMin >= 0) {
            $filters['age_min'] = $ageMin;
        }
    }
    
    if (isset($_GET['age_max'])) {
        $ageMax = filter_var($_GET['age_max'], FILTER_VALIDATE_INT);
        if ($ageMax !== false && $ageMax >= 0) {
            $filters['age_max'] = $ageMax;
        }
    }

    $customers = $customer->getAll($filters);
    $stats = $customer->getStats();
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage());
    $customers = [];
    $stats = [
        'total' => 0,
        'vip' => 0,
        'loyal' => 0,
        'regular' => 0,
        'new' => 0
    ];
}
// Include header and sidebar
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style1.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        
    </style>
</head>
<body>
    
    <main class="main-content">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Quản lý khách hàng</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Tổng khách hàng -->
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-green-500 bg-opacity-10 mr-4">
                    <i data-feather="users" class="w-8 h-8 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Tổng khách hàng</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></h3>
                </div>
            </div>
            
            <!-- Khách hàng VIP -->
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-purple-500 bg-opacity-10 mr-4">
                    <i data-feather="award" class="w-8 h-8 text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Khách hàng VIP</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['vip']); ?></h3>
                    <p class="text-xs text-gray-500">Chi tiêu ≥ 10M</p>
                </div>
            </div>
            
            <!-- Khách hàng thân thiết -->
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-blue-500 bg-opacity-10 mr-4">
                    <i data-feather="star" class="w-8 h-8 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Khách hàng thân thiết</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['loyal']); ?></h3>
                    <p class="text-xs text-gray-500">Chi tiêu 5M - 10M</p>
                </div>
            </div>
            
            <!-- Khách hàng mới -->
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10 mr-4">
                    <i data-feather="user-plus" class="w-8 h-8 text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Khách hàng mới</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['new']); ?></h3>
                    <p class="text-xs text-gray-500">Trong tháng này</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
    
        <!-- Customer Table -->
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Danh sách khách hàng</h2>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <i data-feather="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                        <input type="text" id="searchInput" class="form-input pl-10" placeholder="Tìm kiếm nhanh...">
                    </div>
                    <a href="them_khach_hang.php" class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">
                        <i data-feather="user-plus" class="w-4 h-4 mr-2"></i>
                        Thêm khách hàng
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Lỗi!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Thành công!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="table-header">Khách hàng</th>
                            <th class="table-header">Email</th>
                            <th class="table-header">Số điện thoại</th>
                            <th class="table-header">Lượt mua</th>
                            <th class="table-header">Trạng thái</th>
                            <th class="table-header text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-6">
                                        <i data-feather="users" class="w-12 h-12 text-gray-400 mb-2"></i>
                                        <p class="text-lg font-medium">Không tìm thấy khách hàng nào</p>
                                        <p class="text-sm text-gray-500">Thử thay đổi bộ lọc hoặc thêm khách hàng mới</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="p-3">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                             <i data-feather="user" class="w-5 h-5 text-green-600"></i>
                                            </div>
                                            <div>
                                                <a href="customer_detail.php?id=<?php echo $customer['MaKhachHang']; ?>" 
                                                   class="font-medium text-gray-900 hover:text-green-600 transition-colors duration-200">
                                                    <?php echo htmlspecialchars($customer['TenKhachHang']); ?>
                                                </a>
                                                <div class="text-sm text-gray-500">ID: <?php echo $customer['MaKhachHang']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3"><?php echo htmlspecialchars($customer['Email']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($customer['DienThoai']); ?></td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo isset($customer['SoLuongMua']) ? number_format($customer['SoLuongMua']) : 0; ?>
                                            lượt mua
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span class="status-badge <?php echo $customer['TrangThai'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <i data-feather="<?php echo $customer['TrangThai'] === 'active' ? 'check-circle' : 'x-circle'; ?>" class="w-4 h-4"></i>
                                            <span><?php echo $customer['TrangThai'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?></span>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="sua_khach_hang.php?id=<?php echo $customer['MaKhachHang']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 btn p-2" title="Sửa">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="openChat(<?php echo $customer['MaKhachHang']; ?>, '<?php echo htmlspecialchars($customer['TenKhachHang']); ?>')"
                                                    class="text-green-600 hover:text-green-800 btn p-2" title="Chat">
                                                <i data-feather="message-circle" class="w-4 h-4"></i>
                                            </button>
                                            <a href="purchase_history.php?customer_id=<?php echo $customer['MaKhachHang']; ?>" 
                                               class="text-purple-600 hover:text-purple-800 btn p-2" title="Lịch sử mua hàng">
                                                <i data-feather="shopping-bag" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="if(confirm('Bạn có chắc muốn xóa khách hàng này?')) window.location.href='xoa_khach_hang.php?id=<?php echo $customer['MaKhachHang']; ?>'" 
                                                    class="text-red-600 hover:text-red-800 btn p-2" title="Xóa">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chat Modal -->
        <div id="chatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900" id="chatTitle">Chat với khách hàng</h3>
                    <button onclick="closeChat()" class="text-gray-400 hover:text-gray-500">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-4 h-96 overflow-y-auto" id="chatMessages">
                    <!-- Messages will be loaded here -->
                </div>
                <div class="p-4 border-t">
                    <form id="chatForm" class="flex gap-2">
                        <input type="hidden" id="customerId" name="customer_id" value="">
                        <input type="text" id="messageInput" name="message" 
                               class="flex-1 form-input" placeholder="Nhập tin nhắn...">
                        <button type="submit" class="bg-green-600 text-white btn rounded-lg px-4 py-2">
                            <i data-feather="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Chatbot Floating Button -->
<div class="fixed bottom-10 right-4 z-80">
    <button id="chatbotToggle" class="bg-green-900 text-white rounded-full p-3 shadow-lg hover:bg-green-700 focus:outline-none">
        <i data-feather="message-circle" class="w-9 h-9"></i>
    </button>
</div>

<!-- Chatbot Panel (Hidden by default) -->
<div id="chatbotPanel" class="hidden fixed bottom-16 right-4 w-96 bg-white rounded-lg shadow-xxl z-50">
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-bold text-gray-900">Chatbot Thông Tin Khách Hàng</h2>
        <button id="chatbotClose" class="text-gray-400 hover:text-gray-500">
            <i data-feather="x" class="w-5 h-5"></i>
        </button>
    </div>
    <div class="p-4">
        <div class="mb-4">
            <label for="chatbotCustomerId" class="block text-sm font-medium text-gray-700 mb-2">Chọn khách hàng</label>
            <select id="chatbotCustomerId" class="form-input w-full">
                <option value="">Chọn khách hàng...</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo htmlspecialchars($customer['MaKhachHang']); ?>">
                        <?php echo htmlspecialchars($customer['TenKhachHang']); ?> (ID: <?php echo htmlspecialchars($customer['MaKhachHang']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="chatbotMessages" class="chatbot-panel bg-gray-100 p-4 rounded-lg mb-4 h-64 overflow-y-auto"></div>
        <form id="chatbotForm" class="flex gap-2">
            <input type="text" id="chatbotMessageInput" name="message" class="flex-1 form-input" placeholder="Hỏi thông tin khách hàng... (VD: email, số điện thoại, lượt mua)">
            <button type="submit" class="bg-green-600 text-white btn rounded-lg px-4 py-2">
                <i data-feather="send" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>
    </main>

    <script>
        feather.replace();

        // Chat functionality
        let currentCustomerId = null;
        const chatModal = document.getElementById('chatModal');
        const chatTitle = document.getElementById('chatTitle');
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const customerId = document.getElementById('customerId');
        const messageInput = document.getElementById('messageInput');

        function loadMessages() {
            fetch(`get_messages.php?customer_id=${currentCustomerId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(messages => {
                    if (Array.isArray(messages)) {
                        chatMessages.innerHTML = messages.map(msg => `
                            <div class="mb-4 ${msg.sender_type === 'admin' ? 'text-right' : ''}">
                                <div class="inline-block rounded-lg px-4 py-2 max-w-xs lg:max-w-md ${
                                    msg.sender_type === 'admin' 
                                    ? 'bg-green-600 text-white' 
                                    : 'bg-gray-100 text-gray-800'
                                }">
                                    <p>${msg.message}</p>
                                    <span class="text-xs opacity-75">${msg.sent_at}</span>
                                </div>
                            </div>
                        `).join('');
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    } else {
                        throw new Error('Invalid message format received');
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    chatMessages.innerHTML = '<div class="text-red-500 text-center">Không thể tải tin nhắn. Vui lòng thử lại.</div>';
                });
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            // Disable form while sending
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            messageInput.disabled = true;

            fetch('send_messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `customer_id=${currentCustomerId}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    alert(result.message || 'Không thể gửi tin nhắn. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại.');
            })
            .finally(() => {
                // Re-enable form
                submitButton.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
            });
        });

        // Auto-refresh messages every 5 seconds when chat is open
        let messageInterval;
        
        function startMessagePolling() {
            messageInterval = setInterval(() => {
                if (currentCustomerId) {
                    loadMessages();
                }
            }, 5000);
        }

        function stopMessagePolling() {
            if (messageInterval) {
                clearInterval(messageInterval);
            }
        }

        function openChat(id, name) {
            currentCustomerId = id;
            customerId.value = id;
            chatTitle.textContent = `Chat với ${name}`;
            chatModal.classList.remove('hidden');
            chatModal.classList.add('flex');
            loadMessages();
            messageInput.focus();
            startMessagePolling();
        }

        function closeChat() {
            chatModal.classList.add('hidden');
            chatModal.classList.remove('flex');
            currentCustomerId = null;
            chatMessages.innerHTML = '';
            stopMessagePolling();
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Mobile sidebar toggle
        const toggleSidebar = () => {
            document.querySelector('.sidebar').classList.toggle('show');
        };
        
    </script>
</body>
</html>