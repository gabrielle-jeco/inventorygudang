<!-- Modal Edit Vehicle -->
<div class="modal fade" id="modal_edit_vehicle" tabindex="-1" role="dialog" aria-labelledby="modal_edit_vehicleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_edit_vehicleLabel">Edit Vehicle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form_edit_vehicle">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id">
                    <div class="form-group">
                        <label for="edit_plate_number">Plate Number</label>
                        <input type="text" class="form-control" id="edit_plate_number" name="plate_number" placeholder="Enter plate number">
                        <span class="text-danger" id="edit_plate_number_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="edit_make">Make</label>
                        <input type="text" class="form-control" id="edit_make" name="make" placeholder="Enter vehicle make">
                        <span class="text-danger" id="edit_make_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="edit_model">Model</label>
                        <input type="text" class="form-control" id="edit_model" name="model" placeholder="Enter vehicle model">
                        <span class="text-danger" id="edit_model_error"></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 