@extends('layouts.app')

@section('title', 'Hak Akses')

@push('styles')
<style>
  .hak-akses-card .form-label { font-weight: 600; }
  .permission-table th { white-space: nowrap; }
  .permission-table td { vertical-align: middle; }
  .permission-group { background: #eef4ff; font-weight: 600; }
  .permission-search { max-width: 420px; }
  .permission-table .form-check-input {
    border-width: 2px;
    border-color: #b0b8c5;
  }
  .permission-table .form-check-input:checked {
    border-color: #3b7ddd;
    background-color: #3b7ddd;
  }
</style>
@endpush

@section('content')
  <h1 class="page-title mb-4">Hak Akses</h1>

  <div class="card mb-3 hak-akses-card">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0 text-white">Konfigurasi Hak Akses</h5>
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Role</label>
          <select class="form-select" id="roleSelect">
            <option value="petugas_it">Petugas IT</option>
            <option value="petugas_helpdesk">Petugas Helpdesk</option>
            <option value="manajemen">Manajemen</option>
            <option value="kepala_ruangan">Kepala Ruangan</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Cari menu</label>
          <input type="text" class="form-control permission-search" id="permissionSearch" placeholder="Cari menu...">
        </div>
        <div class="col-md-4 d-flex justify-content-md-end align-items-center gap-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="selectAllPermissions">
            <label class="form-check-label" for="selectAllPermissions">Select all</label>
          </div>
          <button class="btn btn-primary btn-sm" type="button" id="savePermissionsBtn">
            <i data-feather="save"></i> Simpan
          </button>
          <button class="btn btn-outline-primary btn-sm" type="button" id="refreshRoleBtn">
            <i data-feather="refresh-cw"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle permission-table">
          <thead>
            <tr>
              <th>Menu</th>
              <th class="text-center">Read</th>
              <th class="text-center">Create</th>
              <th class="text-center">Update</th>
              <th class="text-center">Delete</th>
            </tr>
          </thead>
          <tbody id="permissionTableBody">
            @foreach($menuGroups as $group => $menus)
              <tr class="permission-group">
                <td colspan="5">{{ $group }}</td>
              </tr>
              @foreach($menus as $menu)
                <tr data-menu="{{ $menu['key'] }}" data-label="{{ $menu['label'] }}">
                  <td>{{ $menu['label'] }}</td>
                  @foreach(['read','create','update','delete'] as $action)
                    <td class="text-center">
                      <input
                        class="form-check-input permission-toggle"
                        type="checkbox"
                        data-action="{{ $action }}"
                        @disabled(!in_array($action, $menu['actions']))
                      >
                    </td>
                  @endforeach
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';
    const roleSelect = document.getElementById('roleSelect');
    const selectAll = document.getElementById('selectAllPermissions');
    const searchInput = document.getElementById('permissionSearch');
    const refreshBtn = document.getElementById('refreshRoleBtn');
    const saveBtn = document.getElementById('savePermissionsBtn');
    const rows = Array.from(document.querySelectorAll('#permissionTableBody tr[data-menu]'));

    function getToggles() {
      return Array.from(document.querySelectorAll('.permission-toggle'));
    }

    function showError(message) {
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'Gagal', text: message });
      } else {
        alert(message);
      }
    }

    function showSuccess(message) {
      if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: message, timer: 1500, showConfirmButton: false });
      }
    }

    async function loadPermissions() {
      const role = roleSelect.value;
      const response = await fetch(`/hak-akses/permissions?role=${encodeURIComponent(role)}`);
      if (!response.ok) {
        showError('Gagal memuat hak akses.');
        return;
      }
      const data = await response.json();
      const permissions = data.permissions || {};

      rows.forEach((row) => {
        const menuKey = row.getAttribute('data-menu');
        const perm = permissions[menuKey] || {};
        row.querySelectorAll('.permission-toggle').forEach((toggle) => {
          const action = toggle.getAttribute('data-action');
          toggle.checked = !!perm[`can_${action}`];
        });
      });
      updateSelectAll();
    }

    function updateSelectAll() {
      const toggles = getToggles().filter((cb) => !cb.disabled);
      if (!selectAll) return;
      selectAll.checked = toggles.length > 0 && toggles.every((cb) => cb.checked);
    }

    if (selectAll) {
      selectAll.addEventListener('change', async function () {
        const value = selectAll.checked;
        const role = roleSelect.value;

        const response = await fetch('/hak-akses/permissions/bulk', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ role, value }),
        });
        if (!response.ok) {
          showError('Gagal menyimpan hak akses.');
          return;
        }
        await loadPermissions();
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        const q = searchInput.value.toLowerCase().trim();
        rows.forEach((row) => {
          const name = row.getAttribute('data-label')?.toLowerCase() ?? '';
          row.style.display = name.includes(q) ? '' : 'none';
        });
      });
    }

    rows.forEach((row) => {
      row.querySelectorAll('.permission-toggle').forEach((toggle) => {
        toggle.addEventListener('change', async function () {
          if (toggle.disabled) return;
          const menu = row.getAttribute('data-menu');
          const action = toggle.getAttribute('data-action');
          const value = toggle.checked;
          const role = roleSelect.value;

          const response = await fetch('/hak-akses/permissions', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ role, menu, action, value }),
          });

          if (!response.ok) {
            toggle.checked = !value;
            showError('Gagal menyimpan hak akses.');
            return;
          }
          updateSelectAll();
        });
      });
    });

    if (roleSelect) {
      roleSelect.addEventListener('change', loadPermissions);
    }

    if (refreshBtn) {
      refreshBtn.addEventListener('click', async function () {
        const role = roleSelect.value;
        const response = await fetch('/hak-akses/permissions/bulk', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ role, value: false }),
        });
        if (!response.ok) {
          showError('Gagal mereset hak akses.');
          return;
        }
        await loadPermissions();
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', async function () {
        const role = roleSelect.value;
        const payload = {};
        rows.forEach((row) => {
          const menuKey = row.getAttribute('data-menu');
          const data = {};
          row.querySelectorAll('.permission-toggle').forEach((toggle) => {
            const action = toggle.getAttribute('data-action');
            data[action] = toggle.checked;
          });
          payload[menuKey] = data;
        });

        const response = await fetch('/hak-akses/permissions/save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ role, permissions: payload }),
        });

        if (!response.ok) {
          showError('Gagal menyimpan hak akses.');
          return;
        }
        showSuccess('Hak akses tersimpan.');
      });
    }

    loadPermissions();
  });
</script>
@endpush
