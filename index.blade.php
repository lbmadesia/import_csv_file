{{-- start import csv for assest --}}
                                <form method="post" action="{{url('import/assetcsv')}}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card shadow">
                                        <div class="card-header">
                                            <h4> Import Users CSV File </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <input type="file" name="csv_file" class="form-control">
                                                {!!$errors->first("csv_file", '<small class="text-danger">:message</small>') !!}
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success" name="submit">Import Data </button>
                                        </div>
                                    </div>
                                </form>
{{-- end import csv for assest --}}
