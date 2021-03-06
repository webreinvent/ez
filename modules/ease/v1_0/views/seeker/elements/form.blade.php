<?php
$output_file='/public/css/rating.css';
?>
<link rel="stylesheet" href="{{ URL::asset($output_file) }}" />
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <div class="col-md-9">
                    {{ Form::hidden('ease_user_id', null, array('class' => 'form-control ',
                    'placeholder' => 'Ease User Id', 'required')) }}
                </div>

                <label class="col-md-3 control-label">Name:</label>
                <div class="col-md-9">
                    {{ Form::text('name', null, array('class' => 'form-control ',
                    'placeholder' => 'Name', 'required')) }}
                </div>

                <label class="col-md-3 control-label">Mobile:</label>
                <div class="col-md-9">
                    {{ Form::text('mobile', null, array('class' => 'form-control ',
                    'placeholder' => 'Mobile', 'required')) }}
                </div>

                <div class="col-md-9">
                    {{ Form::hidden('email', null, array('class' => 'form-control ',
                    'placeholder' => 'Email', 'required')) }}
                </div>

                <label class="col-md-3 control-label">Rating:</label>
                <div class="col-md-9">
                    {{ Form::text('rating', null, array('class' => 'form-control ',
                    'placeholder' => 'Rating', 'required')) }}
                </div>

                <label class="col-md-3 control-label">Wallet:</label>
                <div class="col-md-9">
                    {{ Form::text('wallet', null, array('class' => 'form-control ',
                    'placeholder' => 'Wallet', 'required')) }}
                </div>

                <label class="col-md-3 control-label">Cancellation Amount:</label>
                <div class="col-md-9">
                    {{ Form::text('cancelletion_amount', null, array('class' => 'form-control ',
                    'placeholder' => 'Cancellation Amount', 'required')) }}
                </div>
            </div>

                <div class="form-group col-sm-3">
                    <h4>Verification</h4>
                    <label for="sel1">Select Status:</label>
                    <select class="form-control" id="sel1" name="action">
                        <option selected disabled>Choose here</option>
                        <option value ="false">Reject</option>
                        <option value ="true">Verify</option>
                        <option value ="resend">Image not clear</option>
                    </select>
                </div>

            <div class="form-group col-sm-3">
                <h4>Rating</h4>
                <label for="sel1">Select rating</label>
                <select class="form-control" id="sel1" name="rating">
                    <option selected disabled>Choose here</option>
                    <option value ="1">1</option>
                    <option value ="1.5">1.5</option>
                    <option value ="2">2</option>
                    <option value ="2.5">2.5</option>
                    <option value ="3">3</option>
                    <option value ="3.5">3.5</option>
                    <option value ="4">4</option>
                    <option value ="4.5">4.5</option>
                    <option value ="5">5</option>
                </select>
            </div>


        </div>


    </div>