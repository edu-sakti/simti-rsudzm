@extends('layouts.guest')

@section('title', 'Portal Aplikasi')

@push('styles')
<style>
  :root {
    --apps-bg: #f4f6fb;
    --apps-card: #ffffff;
    --apps-text: #1f2937;
    --apps-sub: #6b7280;
    --apps-border: #e6e9f2;
  }

  body {
    background: var(--apps-bg);
  }

  .apps-page {
    min-height: 100vh;
    padding: 40px 20px 60px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
  }

  .apps-wrap {
    width: 100%;
    max-width: 980px;
    position: relative;
  }

  .apps-hero {
    text-align: center;
    margin-bottom: 24px;
  }

  .apps-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--apps-text);
    margin-bottom: 8px;
  }

  .apps-subtitle {
    color: var(--apps-sub);
    font-size: 0.95rem;
    margin-bottom: 16px;
  }

  .apps-search {
    max-width: 520px;
    margin: 0 auto;
    position: relative;
  }

  .apps-search input {
    width: 100%;
    border: 1px solid var(--apps-border);
    border-radius: 14px;
    padding: 12px 14px 12px 42px;
    background: #fff;
    font-size: 0.95rem;
    color: var(--apps-text);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
  }

  .apps-search svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    width: 18px;
    height: 18px;
  }

  .apps-grid {
    display: grid;
    grid-template-columns: repeat(6, 140px);
    gap: 24px;
    justify-content: center;
    max-width: 900px;
    margin: 0 auto;
  }

  .app-tile {
    width: 140px;
    height: 140px;
    background: var(--apps-card);
    border: 1px solid var(--apps-border);
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .app-tile:hover {
    transform: translateY(-5px) scale(1.03);
    border-color: #d7def0;
    box-shadow: 0 18px 30px rgba(30, 41, 59, 0.12);
  }

  .app-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
  }

  .app-icon svg {
    width: 22px;
    height: 22px;
  }

  .app-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--apps-text);
  }

  .app-cyan { background: linear-gradient(135deg, #22d3ee, #38bdf8); }
  .app-blue { background: linear-gradient(135deg, #60a5fa, #2563eb); }
  .app-indigo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
  .app-violet { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
  .app-emerald { background: linear-gradient(135deg, #34d399, #10b981); }
  .app-amber { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
  .app-rose { background: linear-gradient(135deg, #fb7185, #f43f5e); }
  .app-slate { background: linear-gradient(135deg, #94a3b8, #64748b); }
  .app-teal { background: linear-gradient(135deg, #2dd4bf, #14b8a6); }
  .app-lime { background: linear-gradient(135deg, #a3e635, #65a30d); }
  .app-orange { background: linear-gradient(135deg, #fdba74, #f97316); }
  .app-sky { background: linear-gradient(135deg, #7dd3fc, #0ea5e9); }
  .app-red { background: linear-gradient(135deg, #f87171, #ef4444); }

  @media (max-width: 1100px) {
    .apps-grid { grid-template-columns: repeat(4, 140px); }
  }

  @media (max-width: 860px) {
    .apps-grid { grid-template-columns: repeat(3, 140px); }
  }

  @media (max-width: 640px) {
    .apps-page { padding: 30px 14px 50px; }
    .apps-grid { grid-template-columns: repeat(2, 140px); }
  }
</style>
@endpush

@section('content')
@php
  $user = auth()->user();
  $isAdmin = (bool) ($user->is_admin ?? false) || ($user->role ?? '') === 'admin';
  $can = function (string $menu, string $action = 'read') use ($user) {
    return \App\Support\Permission::can($user, $menu, $action);
  };
@endphp

<div class="apps-page">
  <div class="apps-wrap">
    <div class="apps-hero">
      <div class="apps-title">Portal Aplikasi</div>
      <div class="apps-subtitle">Pilih aplikasi untuk melanjutkan pekerjaan Anda.</div>
      <div class="apps-search">
        <i data-feather="search"></i>
        <input id="appSearch" type="text" placeholder="Cari aplikasi..." autocomplete="off">
      </div>
    </div>

    <div class="apps-grid" id="appsGrid">
      @php
        $appVisible = function (array $menus) use ($can, $isAdmin) {
          if ($isAdmin) {
            return true;
          }
          foreach ($menus as $menu) {
            if ($can($menu)) {
              return true;
            }
          }
          return false;
        };
      @endphp

      @if($appVisible(['dashboard','helpdesk','laporan']))
        <a href="{{ url('/apps/launch/helpdesk') }}" class="app-tile" data-title="helpdesk tiket laporan dashboard">
          <div class="app-icon app-rose"><i data-feather="message-circle"></i></div>
          <div class="app-name">Helpdesk</div>
        </a>
      @endif

      @if($appVisible(['surat_masuk','surat_keluar','disposisi','arsip_surat']))
        <a href="{{ url('/apps/launch/persuratan') }}" class="app-tile" data-title="persuratan surat masuk surat keluar disposisi arsip surat">
          <div class="app-icon app-blue"><i data-feather="mail"></i></div>
          <div class="app-name">Persuratan</div>
        </a>
      @endif


      @if($appVisible(['data_pegawai','pegawai_pns','pegawai_pppk','jabatan','unit_ruangan','riwayat_pegawai','riwayat_pendidikan','riwayat_pangkat','riwayat_mutasi','riwayat_pelatihan','legalitas_sip','legalitas_str']))
        <a href="{{ url('/apps/launch/kepegawaian') }}" class="app-tile" data-title="kepegawaian pegawai pns pppk jabatan unit ruangan riwayat pendidikan pangkat golongan mutasi pelatihan">
          <div class="app-icon app-violet"><i data-feather="users"></i></div>
          <div class="app-name">Kepegawaian</div>
        </a>
      @endif

       @if($appVisible(['pengaduan_data','pengaduan_kategori']))
        <a href="{{ url('/apps/launch/pengaduan') }}" class="app-tile" data-title="pengaduan data pengaduan kategori">
          <div class="app-icon app-amber"><i data-feather="alert-circle"></i></div>
          <div class="app-name">Pengaduan</div>
        </a>
      @endif

      @if($appVisible(['perangkat','ruangan','spesifikasi_perangkat']))
        <a href="{{ url('/apps/launch/inventaris') }}" class="app-tile" data-title="inventaris perangkat ruangan spesifikasi">
          <div class="app-icon app-indigo"><i data-feather="box"></i></div>
          <div class="app-name">Inventaris</div>
        </a>
      @endif

      @if($appVisible(['ip_address','isp']))
        <a href="{{ url('/apps/launch/jaringan') }}" class="app-tile" data-title="jaringan ip address isp">
          <div class="app-icon app-cyan"><i data-feather="wifi"></i></div>
          <div class="app-name">Jaringan</div>
        </a>
      @endif

      @if($appVisible(['cctv']))
        <a href="{{ url('/apps/launch/monitoring') }}" class="app-tile" data-title="cctv monitoring">
          <div class="app-icon app-emerald"><i data-feather="video"></i></div>
          <div class="app-name">CCTV</div>
        </a>
      @endif

      @if($isAdmin)
        <a href="{{ url('/apps/launch/manajemen-pengguna') }}" class="app-tile" data-title="manajemen pengguna hak akses">
          <div class="app-icon app-lime"><i data-feather="users"></i></div>
          <div class="app-name">Pengguna</div>
        </a>
        <a href="{{ url('/apps/launch/integrasi') }}" class="app-tile" data-title="setting wa gateway">
          <div class="app-icon app-slate"><i data-feather="settings"></i></div>
          <div class="app-name">Setting</div>
        </a>
        <a href="{{ url('/auth/logout') }}" class="app-tile" data-title="keluar logout" id="appsLogoutBtn">
          <div class="app-icon app-red"><i data-feather="log-out"></i></div>
          <div class="app-name">Keluar</div>
        </a>
        <form id="apps-logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
          @csrf
        </form>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  if (typeof feather !== 'undefined') feather.replace();

  const input = document.getElementById('appSearch');
  const tiles = Array.from(document.querySelectorAll('.app-tile'));

  if (input) {
    input.addEventListener('input', function () {
      const q = this.value.toLowerCase().trim();
      tiles.forEach((tile) => {
        const title = (tile.dataset.title || '').toLowerCase();
        tile.style.display = title.includes(q) ? '' : 'none';
      });
    });
  }

  const logoutBtn = document.getElementById('appsLogoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function (event) {
      event.preventDefault();
      const submitLogout = () => document.getElementById('apps-logout-form').submit();
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Keluar?',
          text: 'Apakah yakin mau keluar?',
          showCancelButton: true,
          confirmButtonText: 'Ya, keluar',
          cancelButtonText: 'Batal',
        }).then((result) => {
          if (result.isConfirmed) submitLogout();
        });
      } else if (confirm('Apakah yakin mau keluar?')) {
        submitLogout();
      }
    });
  }
</script>
@endpush
