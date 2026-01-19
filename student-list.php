<?php
require_once 'includes/header.php';
require_once 'config/pdo.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Get PDO connection
$pdo = getPDO();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterClass = isset($_GET['class']) ? trim($_GET['class']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(first_name LIKE :search OR last_name LIKE :search OR student_id LIKE :search)";
    $params['search'] = "%{$search}%";
}

if (!empty($filterClass)) {
    $whereConditions[] = "class = :class";
    $params['class'] = $filterClass;
}

if (!empty($filterStatus)) {
    $whereConditions[] = "status = :status";
    $params['status'] = $filterStatus;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total records for pagination
$countSql = "SELECT COUNT(*) as total FROM students {$whereClause}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetch()['total'];
$totalPages = ceil($totalRecords / $records_per_page);

// Get students with pagination
$sql = "SELECT * FROM students {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll();

// Get all unique classes for filter dropdown
$classStmt = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class");
$classes = $classStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-people-fill"></i> Students List</h2>
                    <p class="text-muted mb-0">Manage and view all student records</p>
                </div>
                <div>
                    <a href="views/admission-form.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill"></i> Add New Student
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Error!</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Search and Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="student-list.php" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Search by name or ID..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="col-md-3">
                    <label for="class" class="form-label">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Class
                    </label>
                    <select class="form-select" id="class" name="class">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>"
                                <?php echo $filterClass === $class ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">
                        <i class="bi bi-toggle-on"></i> Status
                    </label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $filterStatus === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="student-list.php" class="btn btn-secondary" title="Clear Filters">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i> Student Records
                    <span class="badge bg-primary"><?php echo $totalRecords; ?></span>
                </h5>
                <div>
                    <button onclick="exportToCSV()" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </button>
                    <button onclick="window.print()" class="btn btn-secondary btn-sm">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="studentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Student ADM</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Parent</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0">No students found</p>
                                        <?php if (!empty($search) || !empty($filterClass) || !empty($filterStatus)): ?>
                                            <p class="small">Try adjusting your filters</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($student['student_id']); ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($student['gender']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-info text-dark">
                                            <?php echo htmlspecialchars($student['class']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="small fw-bold"><?php echo htmlspecialchars($student['guardian_name']); ?></div>

                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="small fw-bold"><?php echo htmlspecialchars($student['guardian_phone']); ?></div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <?php if ($student['status'] === 'active'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-pause-circle"></i> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="views/student-details.php?id=<?php echo $student['id']; ?>"
                                                class="btn btn-outline-info"
                                                title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="views/admission-form.php?id=<?php echo $student['id']; ?>"
                                                class="btn btn-outline-primary"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')"
                                                class="btn btn-outline-danger"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $totalRecords); ?> of <?php echo $totalRecords; ?> records
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <!-- Previous Button -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($filterClass) ? '&class=' . urlencode($filterClass) : ''; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                            // Show max 5 page numbers
                            $start_page = max(1, $page - 2);
                            $end_page = min($totalPages, $page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($filterClass) ? '&class=' . urlencode($filterClass) : ''; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($filterClass) ? '&class=' . urlencode($filterClass) : ''; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Custom Styles for this page -->
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #06b6d4);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table>tbody>tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    @media print {

        .btn,
        .pagination,
        .card-header>div>div:last-child,
        .form-label,
        form {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<!-- JavaScript for Delete Confirmation and CSV Export -->
<script>
    function deleteStudent(id, name) {
        if (confirm(`Are you sure you want to delete ${name}?\n\nThis action cannot be undone.`)) {
            window.location.href = `controllers/student_controller.php?action=delete&id=${id}`;
        }
    }

    function exportToCSV() {
        const table = document.getElementById('studentsTable');
        const rows = table.querySelectorAll('tr');
        const csv = [];

        // Get headers
        const headers = [];
        rows[0].querySelectorAll('th').forEach((th, index) => {
            if (index < rows[0].querySelectorAll('th').length - 1) { // Skip Actions column
                headers.push(th.textContent.trim());
            }
        });
        csv.push(headers.join(','));

        // Get data rows
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cols = row.querySelectorAll('td');

            if (cols.length > 0 && !cols[0].hasAttribute('colspan')) {
                const csvRow = [];
                for (let j = 0; j < cols.length - 1; j++) { // Skip Actions column
                    let cellText = cols[j].textContent.trim();
                    cellText = cellText.replace(/"/g, '""'); // Escape quotes
                    csvRow.push(`"${cellText}"`);
                }
                csv.push(csvRow.join(','));
            }
        }

        // Download CSV
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', `students_${new Date().toISOString().slice(0, 10)}.csv`);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>