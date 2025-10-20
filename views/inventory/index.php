<?php 
$page_title = $data['title'];
$active_page = 'inventory';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse"></i> <?php echo $data['title']; ?></h1>
        <div class="btn-group" role="group">
            <a href="/inventory?type=import" class="btn btn-<?php echo $data['type'] === 'import' ? 'primary' : 'outline-secondary'; ?>">
                <i class="fas fa-arrow-down"></i> Nhập kho
            </a>
            <a href="/inventory?type=export" class="btn btn-<?php echo $data['type'] === 'export' ? 'primary' : 'outline-secondary'; ?>">
                <i class="fas fa-arrow-up"></i> Xuất kho
            </a>
            <a href="/inventory/create?type=<?php echo $data['type']; ?>" class="btn btn-success">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
        </div>
    </div>
    
    <!-- Thống kê nhanh -->
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Tổng <?php echo $data['type'] === 'import' ? 'nhập' : 'xuất'; ?> tháng này</h6>
                            <h3 class="mb-0"><?php 
                                $currentMonth = date('Y-m');
                                $total = 0;
                                foreach ($data['transactions'] as $trans) {
                                    $transDate = $data['type'] === 'import' ? $trans['import_date'] : $trans['export_date'];
                                    if (strpos($transDate, $currentMonth) === 0) {
                                        $total += $trans['quantity'];
                                    }
                                }
                                echo number_format($total);
                            ?></h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Có thể thêm các thẻ thống kê khác tại đây -->
    </div>
</div>

<!-- Bảng danh sách phiếu nhập/xuất -->
<div class="card mt-4">
    <div class="card-body">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($data['transactions'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Ngày</th>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th class="text-end">Số lượng</th>
                            <th>Người thực hiện</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['transactions'] as $trans): 
                            $transDate = $data['type'] === 'import' ? $trans['import_date'] : $trans['export_date'];
                            $formattedDate = date('d/m/Y', strtotime($transDate));
                            $statusClass = $trans['status'] === 'Completed' ? 'success' : 
                                         ($trans['status'] === 'Pending' ? 'warning' : 'danger');
                        ?>
                            <tr>
                                <td><?php echo $data['type'] === 'import' ? $trans['import_code'] : $trans['export_code']; ?></td>
                                <td><?php echo $formattedDate; ?></td>
                                <td><?php echo $trans['product_code']; ?></td>
                                <td><?php echo $trans['product_name']; ?></td>
                                <td class="text-end"><?php echo number_format($trans['quantity']); ?></td>
                                <td><?php echo $data['type'] === 'import' ? $trans['import_by'] : $trans['export_by']; ?></td>
                                <td><span class="badge bg-<?php echo $statusClass; ?>"><?php echo $trans['status']; ?></span></td>
                                <td class="text-center">
                                    <a href="/inventory/<?php echo $trans[$data['type'] . '_id']; ?>?type=<?php echo $data['type']; ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($trans['status'] === 'Pending'): ?>
                                        <a href="#" class="btn btn-sm btn-success" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Phân trang (nếu cần) -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Trước</a>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Tiếp</a>
                    </li>
                </ul>
            </nav>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Chưa có dữ liệu phiếu <?php echo $data['type'] === 'import' ? 'nhập' : 'xuất'; ?> kho nào.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
