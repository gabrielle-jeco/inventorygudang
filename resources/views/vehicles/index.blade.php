@extends('layouts.app')

@include('vehicles.create')
@include('vehicles.edit')

@section('content')
    <div class="section-header">
        <h1>Vehicle Management</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_vehicle"><i class="fa fa-plus"></i> Add Vehicle</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="display">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Plate Number</th>
                                    <th>Make</th>
                                    <th>Model</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let table = $('#table_id').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('vehicles.data') }}",
                type: 'GET'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'plate_number', name: 'plate_number' },
                { data: 'make', name: 'make' },
                { data: 'model', name: 'model' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']]
        });

        // Add Vehicle
        $('#button_tambah_vehicle').click(function() {
            $('#modal_tambah_vehicle').modal('show');
            $('#form_tambah_vehicle')[0].reset();
            $('.text-danger').text('');
        });

        $('#form_tambah_vehicle').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('vehicles.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        $('#modal_tambah_vehicle').modal('hide');
                        $('#form_tambah_vehicle')[0].reset();
                        table.ajax.reload();
                        Swal.fire('Success', 'Vehicle added successfully', 'success');
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#${key}_error`).text(value[0]);
                    });
                }
            });
        });

        // Edit Vehicle
        $(document).on('click', '.edit-vehicle', function() {
            let id = $(this).data('id');
            $('.text-danger').text('');
            $.ajax({
                url: `/vehicles/${id}/edit`,
                method: "GET",
                success: function(response) {
                    $('#edit_id').val(response.id);
                    $('#edit_plate_number').val(response.plate_number);
                    $('#edit_make').val(response.make);
                    $('#edit_model').val(response.model);
                    $('#modal_edit_vehicle').modal('show');
                }
            });
        });

        $('#form_edit_vehicle').submit(function(e) {
            e.preventDefault();
            let id = $('#edit_id').val();
            $.ajax({
                url: `/vehicles/${id}`,
                method: "PUT",
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        $('#modal_edit_vehicle').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success', 'Vehicle updated successfully', 'success');
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#edit_${key}_error`).text(value[0]);
                    });
                }
            });
        });

        // Delete Vehicle
        $(document).on('click', '.delete-vehicle', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/vehicles/${id}`,
                        method: "DELETE",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if(response.success) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', 'Vehicle has been deleted.', 'success');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection 