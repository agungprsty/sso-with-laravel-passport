@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Dashboard Clients') }}
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#generateToken">
                        Generate Token
                    </button>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Clint-ID</th>
                                <th scope="col">Redirect</th>
                                <th scope="col">Secret</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($client as $key => $value)
                            <tr>
                                {{-- <th scope="row">{{$key+1}}</th> --}}
                                <td>{{$value->name}}</td>
                                <td>{{$value->id}}</td>
                                <td>{{$value->redirect}}</td>
                                <td>{{$value->secret}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="generateToken" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Generate Token</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <form action="oauth/clients" method="POST" id="clients">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="col-form-label">Name:</label>
                            <input type="text" name="name" class="form-control" id="name" placeholder="My App" required >
                        </div>
                        <div class="form-group">
                            <label for="redirect" class="col-form-label">Redirect:</label>
                            <textarea class="form-control" name="redirect" id="redirect" placeholder="https://your-app/oauth/callback" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="clients">Generate</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
