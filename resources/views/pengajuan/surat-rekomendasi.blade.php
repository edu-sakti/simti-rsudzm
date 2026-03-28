<form method="POST" action="#" autocomplete="off">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', auth()->user()->name ?? '') }}" placeholder="Masukkan nama">
        </div>

        <div class="col-md-6">
            <label class="form-label">Jabatan</label>
            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" placeholder="Masukkan jabatan">
        </div>

        <div class="col-12">
            <label class="form-label">Isi Rekomendasi</label>
            <textarea name="isi_rekomendasi" class="form-control" rows="4" placeholder="Masukkan isi surat rekomendasi">{{ old('isi_rekomendasi') }}</textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Simpan Pengajuan
            </button>
        </div>
    </div>
</form>
