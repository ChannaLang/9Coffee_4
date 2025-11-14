
<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Products\ProductsController;
use App\Http\Controllers\Admins\AdminsController;
use App\Exports\SalesReportExport;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Admins\AdminPaymentController;
use App\Http\Controllers\Admins\BookingController;
use App\Http\Controllers\Admins\ExpenseController;
use App\Http\Controllers\Admins\OrderController;
use App\Http\Controllers\Admins\ProductController;
use App\Http\Controllers\Admins\RawMaterialController;
use App\Http\Controllers\Admins\ReportController;
use App\Http\Controllers\Admins\StaffController;
use App\Exports\ExpensesExport;


// 🟢 Default Laravel auth
Auth::routes();

// 🟢 Public user pages
Route::get('/', function () {return redirect()->route('view.login');});
    Route::middleware('guest:admin')->group(function () {
    Route::post('/login-user', [ProductsController::class, 'loginUser'])->name('login.user');
    Route::get('admin/login', [AdminsController::class, 'viewLogin'])->name('view.login');
    Route::post('admin/login', [AdminsController::class, 'checkLogin'])->name('check.login');
});



// 🔒 Protected admin routes
    Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminsController::class, 'index'])->name('admins.dashboard');
    Route::post('/logout', [AdminsController::class, 'logout'])->name('admin.logout');
    Route::get('/all-users', [AdminsController::class, 'DisplayAllUsers'])->name('all.users');
    Route::get('/help', [AdminsController::class, 'Help'])->name('admins.help');
    Route::get('admin/low-stock', [ReportController::class, 'lowStock'])->name('admin.low.stock');
    Route::get('admin/reports/sales', [ReportController::class, 'salesReport'])->name('admin.sales.report');
    Route::patch('/products/update-stock/{id}', [ReportController::class, 'updateStock'])->name('admin.product.update-stock');
Route::middleware(['auth:admin', 'superadmin'])->prefix('admin')->group(function () {
    Route::get('/all-admins', [AdminsController::class, 'DisplayAllAdmins'])->name('all.admins');
    Route::get('/create-admins', [AdminsController::class, 'createAdmins'])->name('create.admins');
    Route::post('/create-admins', [AdminsController::class, 'storeAdmins'])->name('store.admins');
    Route::get('/edit-admin/{id}', [AdminsController::class, 'editAdmin'])->name('edit.admin');
    Route::post('/update-admin/{id}', [AdminsController::class, 'updateAdmin'])->name('update.admins');
    Route::delete('/delete-admin/{id}', [AdminsController::class, 'deleteAdmin'])->name('delete.admin');
});

// ✅ Stock Management Routes
    Route::get('/stock', [RawMaterialController::class, 'viewRawMaterials'])->name('admin.raw-material.stock');
    Route::post('/raw-material/store', [RawMaterialController::class, 'store'])->name('raw-material.store');
    Route::patch('/raw-material/update/{id}', [RawMaterialController::class, 'updateMaterial'])->name('raw-material.update');
    Route::post('/raw-material/add/{id}', [RawMaterialController::class, 'addQuantity'])->name('raw-material.add');
    Route::post('/raw-material/reduce/{id}', [RawMaterialController::class, 'reduceQuantity'])->name('raw-material.reduce');
    Route::delete('/raw-material/delete/{id}', [RawMaterialController::class, 'deleteRawMaterial'])->name('raw-material.delete');
    Route::post('products/{id}/add-quantity', [ReportController::class, 'addQuantity'])
    ->name('admin.products.add_quantity');

// ✅ Expense Management Routes
    Route::post('admin/expenses', [ExpenseController::class, 'storeExpense'])->name('admin.expenses.store');
    Route::get('admin/reports/sales/download', function () {return Excel::download(new SalesReportExport, 'sales_report.xlsx');})->name('admin.sales.report.download');
    Route::get('/orders/export', function() {return Excel::download(new OrdersExport, 'orders.xlsx');})->name('orders.export');
    Route::get('admin/expenses', [ExpenseController::class, 'viewExpenses'])->name('admin.expenses');
Route::get('admin/expenses/download', function () {
    return Excel::download(new ExpensesExport, 'expenses.xlsx');
})->name('admin.expenses.download');

    // Orders management
    Route::get('/all-orders', [OrderController::class, 'DisplayAllOrders'])->name('all.orders');
    Route::get('/edit-orders/{id}', [OrderController::class, 'EditOrders'])->name('edit.orders');
    Route::post('/edit-orders/{id}', [OrderController::class, 'UpdateOrders'])->name('update.orders');
    Route::delete('/delete-orders/{id}', [OrderController::class, 'DeleteOrders'])->name('delete.orders');
    Route::delete('/delete-all-orders', [OrderController::class, 'DeleteAllOrders'])->name('delete.all.orders');


    // Products management
    Route::get('/all-products', [ProductController::class, 'DisplayProducts'])->name('all.products');
    Route::get('/create-products', [ProductController::class, 'CreateProducts'])->name('create.products');
    Route::post('/products/store-products', [ProductController::class, 'StoreProducts'])->name('store.products');

    // AJAX edit and delete
    Route::post('/products/{id}/edit-products', [ProductController::class, 'AjaxUpdateProducts'])->name('ajax.edit.products');
    Route::delete('/products/{id}/delete-products', [ProductController::class, 'DeleteProducts'])->name('ajax.delete.products');

Route::prefix('product')->name('admin.product.')->group(function () {
    Route::get('{id}/assign', [ProductController::class, 'showAssignPage'])->name('assignPage');

    // Get all materials for a product (with assigned qty)
    Route::get('{id}/get-materials', [ProductController::class, 'getMaterials'])->name('getMaterials');

    // Get only assigned materials
    Route::get('{id}/get-assigned-materials', [ProductController::class, 'getAssignedMaterials'])->name('getAssignedMaterials');

    // Save assigned materials
    Route::post('{id}/add-materials', [ProductController::class, 'addMaterials'])->name('addMaterials');
    Route::post('{id}/edit-assigned', [ProductController::class, 'editAssigned'])
        ->name('editAssigned');

});

    Route::get('raw-materials/list', [RawMaterialController::class, 'listMaterials']);


    // Bookings management
    Route::get('/all-bookings', [BookingController::class, 'DisplayBookings'])->name('all.bookings');
    Route::get('/edit-bookings/{id}', [BookingController::class, 'EditBookings'])->name('edit.bookings');
    Route::post('/update-bookings/{id}', [BookingController::class, 'UpdateBookings'])->name('update.bookings');
    Route::delete('/delete-bookings/{id}', [BookingController::class, 'DeleteBookings'])->name('delete.bookings');
    Route::get('/create-bookings', [BookingController::class, 'CreateBookings'])->name('create.bookings');
    Route::post('/store-bookings', [BookingController::class, 'StoreBookings'])->name('store.bookings');

    // Payments
    Route::get('/paypal', [AdminPaymentController::class, 'paywithPaypal'])->name('admin.paypal');
    Route::get('/paypal-success', [AdminPaymentController::class, 'paypalSuccess'])->name('admin.paypal.success');
    Route::get('/qr-payment', [AdminPaymentController::class, 'showQrPayment'])->name('admin.qr.payment');

    // Other tools
    Route::get('/staff-sell', [StaffController::class, 'StaffSellForm'])->name('staff.sell.form');
    Route::post('/staff-sell', [StaffController::class, 'StaffSellProduct'])->name('staff.sell');
    Route::post('/staff-checkout', [StaffController::class, 'staffCheckout'])->name('staff.checkout');
});

