<?php 
$page_title = "Panel de Vendedor | VIVA - Artesanías Colombianas";
$body_class = "bg-gray-100 font-sans antialiased";
require_once __DIR__ . '/partials/base_head.php'; 
?>
    
    <div class="flex h-screen overflow-hidden">
        
        <?php require_once __DIR__ . '/mis_productos/sidebar.view.php'; ?>
        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
            
            <?php require_once __DIR__ . '/mis_productos/header.view.php'; ?>
            
            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 bg-cover bg-center" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent), url('<?= BASE_URL ?>images/background_registro_vender.webp');">   
                <?php 
                    if (isset($current_controller) && file_exists($current_controller)) {
                        require_once $current_controller;
                    } else {
                        echo '<div class="p-6 bg-white rounded-xl shadow-lg text-center"><p class="text-red-500">Error: Vista no encontrada.</p></div>';
                    }
                ?>
            </main>
        </div>
    </div>

    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>
    <!-- SweetAlert2 for Delete Confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= BASE_URL ?>src/scripts/toast.js"></script>
</body>
</html>
