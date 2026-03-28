@extends('layouts.app')

@section('title', 'Pengajuan')

@section('content')
<style>
    .accordion-premium {
        --acc-bg: #ffffff;
        --acc-bg-hover: #f9fafb;
        --acc-border: #e5e7eb;
        --acc-text: #1f2937;
        --acc-body-bg: #ffffff;
        --acc-radius: 8px;
        --acc-transition: all 0.25s ease;
        --acc-active: #e7f1ff;
    }

    .accordion-premium .accordion-item {
        background: var(--acc-bg);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius);
        overflow: hidden;
        margin-bottom: 10px;
        transition: var(--acc-transition);
        position: relative;
    }

    .accordion-premium .accordion-item:last-child {
        margin-bottom: 0;
    }

    .accordion-premium .accordion-item:hover {
        border-color: #d5d5d5;
    }

    .accordion-premium .accordion-item.active-item {
        border-color: #bfdbfe;
        background: var(--acc-active);
    }

    .accordion-premium .accordion-header {
        width: 100%;
        margin: 0;
    }

    .accordion-premium .accordion-button {
        width: 100%;
        min-height: 58px;
        padding: 0 18px;
        background: var(--acc-bg);
        color: var(--acc-text);
        font-size: 16px;
        font-weight: 600;
        line-height: 1.4;
        border: 0;
        box-shadow: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 2;
        transition: var(--acc-transition);
    }

    .accordion-premium .accordion-button:not(.collapsed) {
        background: var(--acc-active);
        color: var(--acc-text);
        box-shadow: none;
    }

    .accordion-premium .accordion-button:hover {
        background: var(--acc-bg-hover);
    }

    .accordion-premium .accordion-button:focus {
        box-shadow: none;
    }

    .accordion-premium .accordion-button::after {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        margin-left: auto;
        background-size: 20px;
        transition: transform 0.28s ease;
    }

    .accordion-premium .accordion-button > span {
        display: inline-block;
        transition: transform 0.2s ease;
    }

    .accordion-premium .accordion-item:hover .accordion-button > span {
        transform: translateX(2px);
    }

    .accordion-premium .accordion-collapse {
        border-top: 1px solid #ececec;
    }

    .accordion-premium .accordion-body {
        background: var(--acc-active);
        padding: 16px 18px;
        animation: fadeSlideDown 0.28s ease;
    }

    @keyframes fadeSlideDown {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pengajuan-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: none;
    }

    .pengajuan-card .card-header {
        background: #fff;
        border-bottom: 0;
        padding-bottom: 0;
    }

    .pengajuan-card .card-body {
        padding-top: 14px;
    }

    @media (max-width: 768px) {
        .accordion-premium .accordion-button {
            min-height: 52px;
            padding: 0 14px;
            font-size: 15px;
        }

        .accordion-premium .accordion-body {
            padding: 12px 14px;
        }
    }
</style>

<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Pengajuan</h1>

    <div class="card pengajuan-card">
        <div class="card-body">
            <div class="accordion accordion-premium" id="accordionPengajuan">

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSuratCuti">
                        <button
                            class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseSuratCuti"
                            aria-expanded="false"
                            aria-controls="collapseSuratCuti">
                            <span>Surat Cuti</span>
                        </button>
                    </h2>
                    <div
                        id="collapseSuratCuti"
                        class="accordion-collapse collapse"
                        aria-labelledby="headingSuratCuti"
                        data-bs-parent="#accordionPengajuan">
                        <div class="accordion-body">
                            @include('pengajuan.surat-cuti')
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSuratRekomendasi">
                        <button
                            class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseSuratRekomendasi"
                            aria-expanded="false"
                            aria-controls="collapseSuratRekomendasi">
                            <span>Surat Rekomendasi</span>
                        </button>
                    </h2>
                    <div
                        id="collapseSuratRekomendasi"
                        class="accordion-collapse collapse"
                        aria-labelledby="headingSuratRekomendasi"
                        data-bs-parent="#accordionPengajuan">
                        <div class="accordion-body">
                            @include('pengajuan.surat-rekomendasi')
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const accordionItems = document.querySelectorAll('#accordionPengajuan .accordion-item');

        accordionItems.forEach((item) => {
            const collapseEl = item.querySelector('.accordion-collapse');

            collapseEl.addEventListener('show.bs.collapse', function () {
                item.classList.add('active-item');
            });

            collapseEl.addEventListener('hide.bs.collapse', function () {
                item.classList.remove('active-item');
            });

            if (collapseEl.classList.contains('show')) {
                item.classList.add('active-item');
            }
        });
    });
</script>
@endsection
