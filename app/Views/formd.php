<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles mx-0">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Hitung Harga Setelah Diskon</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            
                            <!-- Harga Awal -->
                            <div class="form-group">
                                <h6 class="text-label">Harga Awal</h6>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-money-bill"></i></span>
                                    </div>
                                    <input type="number" class="form-control" id="harga_awal" placeholder="Masukkan harga" min="1">
                                </div>
                            </div>

                            <!-- Diskon -->
                            <div class="form-group">
                                <h6 class="text-label">Diskon (%)</h6>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-percent"></i></span>
                                    </div>
                                    <input type="number" class="form-control" id="diskon" placeholder="Masukkan diskon" min="0" max="100">
                                </div>
                            </div>

                            <!-- Harga Setelah Diskon -->
                            <div class="form-group">
                                <h6 class="text-label">Harga Setelah Diskon</h6>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-tag"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="harga_setelah_diskon" readonly>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('diskon').addEventListener('input', hitungHarga);
    document.getElementById('harga_awal').addEventListener('input', hitungHarga);

    function hitungHarga() {
        let hargaAwal = parseFloat(document.getElementById('harga_awal').value) || 0;
        let diskon = parseFloat(document.getElementById('diskon').value) || 0;

        if (hargaAwal > 0 && diskon >= 0 && diskon <= 100) {
            let hargaDiskon = hargaAwal - (hargaAwal * diskon / 100);
            document.getElementById('harga_setelah_diskon').value = hargaDiskon.toFixed(2);
        } else {
            document.getElementById('harga_setelah_diskon').value = '';
        }
    }
</script>
