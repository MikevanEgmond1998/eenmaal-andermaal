@extends('layouts.app')

@section('content')



    <div class="container">
        <h2 class="text-center my-4">Veiling maken</h2>

        <div class="card my-3">
            <div class="card-body">
                <form class="make-auction" method="POST" action="/auctions">
                    @csrf

                    <div class="row">

                        <h3>Wat wil je veilen?</h3>

                        <div class="mb-3 col-md-12">
                            <label for="title" class="form-label">Vul een titel in</label>
                            <input name="inputTitle" type="text" class="form-control" id="title">
                        </div>
                        <label for="name" class="form-label">Kies een rubriek</label>
                        <div class="mb-3 col-md-4">
                            <select class="form-select" aria-label="Default select example">
                                <option selected>Kies groep</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-4">
                            <select class="form-select" aria-label="Default select example">
                                <option selected>Kies subgroep</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-4">
                            <select class="form-select" aria-label="Default select example">
                                <option selected>Kies subgroep</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>

                        <h3 class="my-3">Details</h3>
                        <label for="formFile" class="form-label">Foto's</label>
                        <i>plaats hier de foto's van je product</i>
                        <div class="mb-3">
                            <input class="form-control" type="file" id="formFileMultiple" multiple>
                        </div>

                        <label for="inputDescription" class="form-label">Omschrijving</label>
                        <i>geef hier een omschrijving van je product</i>
                        <div class="mb-3 col-md-12">
                            <textarea name="inputDescription" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                        </div>

                        <label for="inputDuration" class="form-label">Veiling duur</label>
                        <i>vul hier het aantal dagen van je veiling in</i>
                        <div class="mb-3 col-md-12">
                            <input type="number" value="1" name="inputDuration" id="inputDuration" class="form-control">
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="inputCountryCode" class="form-label">Landcode</label>
                            <select name="inputCountryCode" class="form-select" aria-label="Default select example">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->country_code }}">{{ $country->country_code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-4">
                        <label for="inputCity" class="form-label">Stad</label>
                            <input type="text" name="inputCity" id="inputCity" class="form-control">
                        </div>


                        <h3 class="my-3">Betaling</h3>

                        <div class="col-md-12">
                            <label for="inputPaymentInstruction" class="form-label">Extra betalingsinstructies</label>
                                <textarea name="inputPaymentInstruction" class="form-control"></textarea>
                        </div>

                        <div class="col-md-3">
                            <label for="inputStartPrice" class="form-label">Startprijs</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">€</span>
                                    <input name="inputStartPrice" type="number" class="form-control">
                                <span class="input-group-text">.00</span>
                            </div>
                        </div>

                        <div class="mb-3 col-md-12">
                            <label for="name" class="form-label">Betaalmethodes</label>
                            @foreach ($paymentMethods as $paymentMethod)
                            <div class="form-check">

                                    <input class="form-check-input" type="checkbox" value="{{ $paymentMethod->id }}" id="flexCheckDefault">

                                    <label class="form-check-label" for="flexCheckDefault">
                                        {{ $paymentMethod->method }}
                                    </label>
                            </div>
                            @endforeach
                        </div>

                        <h3 class="my-3">Levering</h3>

                        <div class="mb-3 col-md-12">
                            <label for="name" class="form-label">Betaalmethodes</label>
                            @foreach ($shippingMethods as $shippingMethod)
                                <div class="form-check">

                                    <input class="form-check-input" type="checkbox" value="{{ $shippingMethod->id }}" id="flexCheckDefault">

                                    <label class="form-check-label" for="flexCheckDefault">
                                        {{ $shippingMethod->method  }}
                                    </label>
                                </div>
                            @endforeach
                        </div>


                    </div>
                    <button type="submit" class="btn btn-primary">Veiling aanmaken</button>
                </form>
            </div>
        </div>
    </div>

@endsection
