<tr class="dynamic-form">
    <td width="250">
        <select class="form-control select2 produto_id" name="produto_id[]" id="inp-produto_id">
            <option value="{{ $prod->produto_id }}">{{ $prod->produto->nome }}</option>
        </select>
        <div style="width: 400px;"></div>
    </td>
    <td width="80">
        <input style="width: 120px" value="{{ __moeda($prod->quantidade) }}" class="form-control qtd" type="tel" name="quantidade[]" id="inp-quantidade">
    </td>
    <td width="100">
        <input style="width: 120px" value="{{ __moeda($prod->valor_unitario) }}" class="form-control moeda valor_unit" type="tel" name="valor_unitario[]" id="inp-valor_unitario">
    </td>
    <td width="150">
        <input style="width: 120px" value="{{ __moeda($prod->sub_total) }}" class="form-control moeda sub_total" type="tel" name="sub_total[]" id="inp-subtotal">
    </td>
    <td width="80">
        <input style="width: 120px" value="{{ $prod->produto->perc_icms }}" class="form-control percentual" type="tel" name="perc_icms[]" id="inp-perc_icms">
    </td>
    <td width="80">
        <input style="width: 120px" value="{{ $prod->produto->perc_pis }}" class="form-control percentual" type="tel" name="perc_pis[]" id="inp-perc_pis">
    </td>
    <td width="80">
        <input style="width: 120px" value="{{ $prod->produto->perc_cofins }}" class="form-control percentual" type="tel" name="perc_cofins[]" id="inp-perc_cofins">
    </td>
    <td width="80">
        <input style="width: 120px" value="{{ $prod->produto->perc_ipi }}" class="form-control percentual" type="tel" name="perc_ipi[]" id="inp-perc_ipi">
    </td>
    <td width="80">
        <input style="width: 120px" value="{{ $prod->produto->perc_red_bc }}" class="form-control percentual ignore" type="tel" name="perc_red_bc[]" id="inp-perc_red_bc">
    </td>
    <td width="80">
        @if($mesmo_estado)
        <input style="width: 120px" value="{{ $prod->produto->cfop_entrada_estadual ?? preg_replace('/^\d/', '1', $prod->saida->cfop_outro_estado) }}" class="form-control cfop" type="tel" name="cfop[]" id="inp-cfop_estadual">
        @else
        <input style="width: 120px" value="{{ $prod->produto->cfop_entrada_outro_estado ?? preg_replace('/^\d/', '2', $prod->produto->cfop_outro_estado) }}" class="form-control cfop" type="tel" name="cfop[]" id="inp-cfop_estadual">
        @endif
    </td>

    <td width="120">
        <input style="width: 120px" value="{{ $prod->produto->ncm }}" class="form-control ncm" type="tel" name="ncm[]" id="inp-ncm2">
    </td>
    <td width="120">
        <input style="width: 120px" value="{{ $prod->produto->codigo_beneficio_fiscal }}" class="form-control ignore codigo_beneficio_fiscal" type="text" name="codigo_beneficio_fiscal[]">
    </td>

    <td width="250">
        <select name="cst_csosn[]" class="form-control select2">
            @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
            <option @if($prod->produto->cst_csosn == $key) selected @endif value="{{$key}}">{{$c}}</option>
            @endforeach
        </select>
        <div style="width: 250px;"></div>
    </td>
    <td width="250">
        <select name="cst_pis[]" class="form-control select2">
            @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
            <option @if($prod->produto->cst_pis == $key) selected @endif value="{{$key}}">{{$c}}</option>
            @endforeach
        </select>
        <div style="width: 250px;"></div>
    </td>
    <td width="250">
        <select name="cst_cofins[]" class="form-control select2">
            @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
            <option @if($prod->produto->cst_cofins == $key) selected @endif value="{{$key}}">{{$c}}</option>
            @endforeach
        </select>
        <div style="width: 250px;"></div>
    </td>
    <td width="250">
        <select name="cst_ipi[]" class="form-control select2">
            @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
            <option @if($prod->produto->cst_ipi == $key) selected @endif value="{{$key}}">{{$c}}</option>
            @endforeach
        </select>
        <div style="width: 250px;"></div>
    </td>
    <td>
        <input style="width: 200px;" class="form-control" maxlength="15" type="text" name="xPed[]">
    </td>
    <td>
        <input style="width: 200px;" class="form-control" maxlength="6" type="text" name="nItemPed[]">
    </td>
    <td>
        <input style="width: 300px;" class="form-control ignore" maxlength="200" type="text" name="infAdProd[]">
    </td>
    <td width="30">
        <button class="btn btn-danger btn-remove-tr">
            <i class="ri-delete-bin-line"></i>
        </button>
    </td>
</tr>