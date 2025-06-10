<!-- Modal Tambah Vehicle -->
<div class="modal fade" id="modal_tambah_vehicle" tabindex="-1" role="dialog" aria-labelledby="modal_tambah_vehicleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_tambah_vehicleLabel">Add New Vehicle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form_tambah_vehicle">
                    @csrf
                    <div class="form-group">
                        <label for="plate_number">Plate Number</label>
                        <input type="text" class="form-control" id="plate_number" name="plate_number" placeholder="Enter plate number">
                        <span class="text-danger" id="plate_number_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="make">Make</label>
                        <input type="text" class="form-control" id="make" name="make" placeholder="Enter vehicle make">
                        <span class="text-danger" id="make_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" class="form-control" id="model" name="model" placeholder="Enter vehicle model">
                        <span class="text-danger" id="model_error"></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 