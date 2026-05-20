<label for="{{$id}}" class="block mb-1 text-md font-medium ms-2">{{$name}}</label>
<div class="relative">
    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        {{$slot}}
    </div>
    <input type="text" id="{{$id}}" class="block w-full ps-9 pe-3 py-2.5  bg-neutral-200/40 border text-md rounded-xl focus:border-[var(--accent)]  shadow-md placeholder:text-body" placeholder="{{$name}}">
</div>
