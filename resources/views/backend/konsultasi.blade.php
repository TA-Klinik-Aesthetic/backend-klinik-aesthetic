@extends('backend.index')

@section('content')

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Pasien</h1>

    <!-- DataTables Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">DataTables Pasien</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" >
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Tanggal Lahir</th>
                            <th>Kelamin</th>
                            <th>No. Telp</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pasiens as $pasien)
                        <tr>
                            <td>{{ $pasien->id_pasien }}</td>
                            <td>{{ $pasien->nama_pasien }}</td>
                            <td>{{ $pasien->nik }}</td>
                            <td>{{ $pasien->tgl_lahir }}</td>
                            <td>{{ $pasien->jk }}</td>
                            <td>{{ $pasien->no_tlp }}</td>
                            <td>{{ $pasien->email }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection


