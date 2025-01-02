<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @can(\App\Models\Permission::UPDATE_TEAM, $team)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('team.partials.update-team-form')
                    </div>
                </div>
            @endcan

            @can(\App\Models\Permission::VIEW_TEAM_MEMBERS, $team)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('team.partials.team-members')
                    </div>
                </div>
            @endcan

            @can('leaveTeam', $team)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <form action="{{ route('team.leave', $team) }}" method="post">
                            @csrf
                            <x-danger-button>Leave team</x-danger-button>
                        </form>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
