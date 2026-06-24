</div>
        <footer class="sticky-footer bg-white shadow-sm mt-4">
            <div class="container my-auto">
                <div class="copyright text-center my-auto font-weight-bold">
                    <span class="text-muted">Hệ thống Quản lý Sinh viên &copy; 2026</span>
                </div>
            </div>
        </footer>
        </div>
    </div>
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="logoutModalLabel">Xác nhận Đăng xuất</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-sign-out-alt fa-3x text-warning mb-3"></i>
                <h5 class="font-weight-bold text-gray-800">Bạn muốn kết thúc phiên làm việc?</h5>
                <p class="mb-0 text-muted">Chọn "Đăng xuất" bên dưới để trở về màn hình đăng nhập.</p>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary font-weight-bold" type="button" data-dismiss="modal">Hủy bỏ</button>
                <a class="btn btn-danger font-weight-bold" href="<?= BASE_URL ?>auth/logout.php">Đăng xuất ngay</a>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>vendor/jquery/jquery.min.js"></script>
<script src="<?= BASE_URL ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= BASE_URL ?>js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>