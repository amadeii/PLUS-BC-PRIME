<div class="row">
    @foreach($data as $prod)
    <div class="col-6 col-md-2 mb-2">
        <div class="card-group pizza-card" onclick="selectPizza('{{ $prod->id }}')">
            <div class="row bg-{{ $prod->id }} @if($prod->id == $produto_id) bg-info @endif pizza-card-inner">

                <img src="{{$prod->img}}" 
                     class="card-img-top mt-1 pizza-img-mini" 
                     alt="{{$prod->nome}}">

                <div class="row mt-1">
                    <p class="text-center text-black pizza-nome">
                        {{$prod->nome}}
                    </p>
                </div>

                <div class="row">
                    <p class="text-center fw-bold pizza-valor">
                        R$ {{ __moeda($prod->valorPizza($tamanho_id)) }}
                    </p>
                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>