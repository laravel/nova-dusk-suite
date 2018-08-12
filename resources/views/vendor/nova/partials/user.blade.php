<div slot="default" class="flex items-center" v-pre>
    <img src="https://secure.gravatar.com/avatar/{{ md5(auth()->user()->email) }}?size=512" class="rounded-full w-8 h-8 mr-3"/>

    <span class="text-90">
        {{ auth()->user()->name }}
    </span>
</div>

<div slot="menu">
    <ul class="list-reset">
        <li>
            <a href="{{ Laravel\Nova\Nova::path() }}/logout" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
    </ul>
</div>
