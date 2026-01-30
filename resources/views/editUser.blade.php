@extends('layouts.admin')

@section('content')

<h1 class="text-center text-black text-3xl p-2">Editar Usuario</h1>


<div class="flex justify-center py-2">
<form method="post" action="{{ route('users.update') }}" class="bg-[#2f2f2f] p-6 rounded-2xl shadow-lg w-full max-w-md">
    @csrf
    <input type="hidden" name="id" value="{{ $userToEdit->id }}">

    <label class="text-white">Nombre:</label>
    <input type="text" name="name" value="{{ $userToEdit->name }}" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">

    <label class="text-white">Email:</label>
    <input type="email" name="email" value="{{ $userToEdit->email }}" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">

    <label class="text-white">Teléfono:</label>
    <input type="text" name="phone" value="{{ preg_replace('/^52/', '', $userToEdit->phone) }}" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">

        @php
        // Convertimos el campo proyect a array
        $selectedProyects = json_decode($userToEdit->proyect, true) ?? [];
        if (!is_array($selectedProyects)) {
            $selectedProyects = [$userToEdit->proyect]; // por si está en string
        }
    @endphp

    <div class="mb-4 mt-4">
        <label class="text-white">Proyectos</label>
        <select name="proyect[]" id="proyect" multiple required  multiselect-hide-x="true" class="text-black">
            <option value="RESIDENT 1" {{ in_array("RESIDENT 1", $selectedProyects) ? 'selected' : '' }}>RESIDENT 1</option>
            <option value="RESIDENT 2" {{ in_array("RESIDENT 2", $selectedProyects) ? 'selected' : '' }}>RESIDENT 2</option>
            <option value="CAMPUS RECIDENCIA" {{ in_array("CAMPUS RECIDENCIA", $selectedProyects) ? 'selected' : '' }}>CAMPUS RECIDENCIA</option>
            <option value="TMZN 122" {{ in_array("TMZN 122", $selectedProyects) ? 'selected' : '' }}>TMZN 122</option>
            <option value="GRAND TEMOZON" {{ in_array("GRAND TEMOZON", $selectedProyects) ? 'selected' : '' }}>GRAND TEMOZON</option>
            <option value="MB RESORT MERIDA" {{ in_array("MB RESORT MERIDA", $selectedProyects) ? 'selected' : '' }}>MB RESORT MÉRIDA</option>
            <option value="Princess Village" {{ in_array("Princess Village", $selectedProyects) ? 'selected' : '' }}>Princess Village</option>
            <option value="Royal Square Plaza" {{ in_array("Royal Square Plaza", $selectedProyects) ? 'selected' : '' }}>Royal Square Plaza</option>
            <option value="RUM" {{ in_array("RUM", $selectedProyects) ? 'selected' : '' }}>RUM</option>
            <option value="Avenue Temozon" {{ in_array("Avenue Temozon", $selectedProyects) ? 'selected' : '' }}>Avenue Temozón</option>
            <option value="MB Resort Orlando" {{ in_array("MB Resort Orlando", $selectedProyects) ? 'selected' : '' }}>MB Resort Orlando</option>
            <option value="MB Wellness Resort" {{ in_array("MB Wellness Resort", $selectedProyects) ? 'selected' : '' }}>MB Wellness Resort</option>
            <option value="Aldea Borboleta I" {{ in_array("Aldea Borboleta I", $selectedProyects) ? 'selected' : '' }}>Aldea Borboleta I</option>
            <option value="Aldea Borboleta II" {{ in_array("Aldea Borboleta II", $selectedProyects) ? 'selected' : '' }}>Aldea Borboleta II </option>
            <option value="Aldea Borboleta III" {{ in_array("Aldea Borboleta III", $selectedProyects) ? 'selected' : '' }}>Aldea Borboleta III</option>
          </select>
    </div>

        <div class="mb-4 mt-4">
        <label class="text-white">Regimen Fiscal</label>
        <select name="regimenFiscal" id="regimenFiscal" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200 text-black">
            <option value="resico">RESICO</option>
            <option value="arrendamiento">ARRENDAMIENTO</option>
            <option value="persona moral">PERSONA MORAL</option>
        </select>
    </div>


    <label class="text-white">Nueva Contraseña (opcional):</label>
    <input type="password" name="password" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">

    <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] px-4 py-2 rounded mt-6">Guardar</button>
</form>
</div>

<script>
function MultiselectDropdown(options){
  var config={
    search:true,
    height:'15rem',
    placeholder:'selecciona',
    txtSelected:'seleccionados',
    txtAll:'Todos',
    txtRemove: 'Quitar',
    txtSearch:'Buscar...',
    ...options
  };

  function newEl(tag,attrs){
    var e=document.createElement(tag);
    if(attrs!==undefined) Object.keys(attrs).forEach(k=>{
      if(k==='class') { 
        Array.isArray(attrs[k]) 
          ? attrs[k].forEach(o=>o!==''?e.classList.add(o):0) 
          : (attrs[k]!==''?e.classList.add(attrs[k]):0)
      }
      else if(k==='style'){  
        Object.keys(attrs[k]).forEach(ks=>{
          e.style[ks]=attrs[k][ks];
        });
       }
      else if(k==='text'){attrs[k]===''?e.innerHTML='&nbsp;':e.innerText=attrs[k]}
      else e[k]=attrs[k];
    });
    return e;
  }

  document.querySelectorAll("select[multiple]").forEach((el,k)=>{
    var div=newEl('div',{class:'multiselect-dropdown',style:{width:config.style?.width??el.clientWidth+'px',padding:config.style?.padding??''}});
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    el.style.visibility = 'hidden';
    el.style.height = 0;
    el.style.width = 0;
    el.style.pointerEvents = 'none';
    el.parentNode.insertBefore(div,el.nextSibling);
    var listWrap=newEl('div',{class:'multiselect-dropdown-list-wrapper'});
    var list=newEl('div',{class:'multiselect-dropdown-list',style:{height:config.height}});
    var search=newEl('input',{class:['multiselect-dropdown-search'],style:{width:'95%',display:el.attributes['multiselect-search']?.value==='true'?'block':'none'},placeholder:config.txtSearch});
    listWrap.appendChild(search);
    div.appendChild(listWrap);
    listWrap.appendChild(list);

    el.loadOptions=()=>{
      list.innerHTML='';
      
      if(el.attributes['multiselect-select-all']?.value=='true'){
        var op=newEl('div',{class:'multiselect-dropdown-all-selector'})
        var ic=newEl('input',{type:'checkbox'});
        op.appendChild(ic);
        op.appendChild(newEl('label',{text:config.txtAll}));
  
        op.addEventListener('click',()=>{
          op.classList.toggle('checked');
          op.querySelector("input").checked=!op.querySelector("input").checked;
          
          var ch=op.querySelector("input").checked;
          list.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)")
            .forEach(i=>{if(i.style.display!=='none'){i.querySelector("input").checked=ch; i.optEl.selected=ch}});
  
          el.dispatchEvent(new Event('change'));
        });
        ic.addEventListener('click',(ev)=>{ ic.checked=!ic.checked; });
        el.addEventListener('change', (ev)=>{
          let itms=Array.from(list.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)")).filter(e=>e.style.display!=='none')
          let existsNotSelected=itms.find(i=>!i.querySelector("input").checked);
          if(ic.checked && existsNotSelected) ic.checked=false;
          else if(ic.checked==false && existsNotSelected===undefined) ic.checked=true;
        });
  
        list.appendChild(op);
      }

      Array.from(el.options).map(o=>{
        var op=newEl('div',{class:o.selected?'checked':'',optEl:o})
        var ic=newEl('input',{type:'checkbox',checked:o.selected});
        op.appendChild(ic);
        op.appendChild(newEl('label',{text:o.text}));

        op.addEventListener('click',()=>{
          op.classList.toggle('checked');
          op.querySelector("input").checked=!op.querySelector("input").checked;
          op.optEl.selected=!!!op.optEl.selected;
          el.dispatchEvent(new Event('change'));
        });
        ic.addEventListener('click',(ev)=>{ ic.checked=!ic.checked; });
        o.listitemEl=op;
        list.appendChild(op);
      });
      div.listEl=listWrap;

      div.refresh=()=>{
        div.querySelectorAll('span.optext, span.placeholder').forEach(t=>div.removeChild(t));
        var sels=Array.from(el.selectedOptions);
        if(sels.length>(el.attributes['multiselect-max-items']?.value??2)){
          div.appendChild(newEl('span',{class:['optext','maxselected'],text:sels.length+' '+config.txtSelected}));          
        }
        else{
          sels.map(x=>{
            var c=newEl('span',{class:'optext',text:x.text, srcOption: x});
            if((el.attributes['multiselect-hide-x']?.value !== 'true'))
              c.appendChild(newEl('span',{class:'optdel',text:'🗙',title:config.txtRemove, onclick:(ev)=>{c.srcOption.listitemEl.dispatchEvent(new Event('click'));div.refresh();ev.stopPropagation();}}));

            div.appendChild(c);
          });
        }
        if(0==el.selectedOptions.length) div.appendChild(newEl('span',{class:'placeholder',text:el.attributes['placeholder']?.value??config.placeholder}));
      };
      div.refresh();
    }
    el.loadOptions();
    
    search.addEventListener('input',()=>{
      list.querySelectorAll(":scope div:not(.multiselect-dropdown-all-selector)").forEach(d=>{
        var txt=d.querySelector("label").innerText.toUpperCase();
        d.style.display=txt.includes(search.value.toUpperCase())?'flex':'none';
      });
    });

    div.addEventListener('click',()=>{
      div.listEl.style.display='block';
      search.focus();
      search.select();
    });
    
    document.addEventListener('click', function(event) {
      if (!div.contains(event.target)) {
        listWrap.style.display='none';
        div.refresh();
      }
    });    
  });
}

window.addEventListener('load',()=>{
  MultiselectDropdown(window.MultiselectDropdownOptions);
});
</script>

@endsection
