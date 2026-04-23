 <!-- Header -->
 <header id="page-header">
     <!-- Header Content -->
     <div class="content-header">
         <!-- Left Section -->
         <div class="space-x-1">
             <!-- Toggle Sidebar -->
             <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
             <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="layout" data-action="sidebar_toggle">
                 <i class="fa fa-fw fa-bars"></i> <?= tanggal_indonesia(date('Y-m-d')); ?>
             </button>
             <!-- END Toggle Sidebar -->
         </div>
         <!-- END Left Section -->

         <!-- Right Section -->
         <div class="space-x-1">
             <!-- User Dropdown -->
             <div class="dropdown d-inline-block">
                 <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i class="fa fa-user d-sm-none"></i>
                     <span class="d-none d-sm-inline-block fw-semibold"><?= huruf_besar_awal(session()->username); ?></span>
                     <i class="fa fa-angle-down opacity-50 ms-1"></i>
                 </button>
                 <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0" aria-labelledby="page-header-user-dropdown">
                     <div class="px-2 py-3 bg-body-light rounded-top">
                         <h5 class="h6 text-center mb-0">
                             Administrator
                         </h5>
                     </div>
                     <div class="p-2">
                         <a class="dropdown-item d-flex align-items-center justify-content-between space-x-1" href="<?= base_url('admin/logout'); ?>">
                             <span>Sign Out</span>
                             <i class="fa fa-fw fa-sign-out-alt opacity-25"></i>
                         </a>
                     </div>
                 </div>
             </div>
             <!-- END User Dropdown -->
         </div>
         <!-- END Right Section -->
     </div>
     <!-- END Header Content -->
 </header>
 <!-- END Header -->