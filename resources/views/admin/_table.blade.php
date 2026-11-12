<div class="table-responsive">
  <table class="table table-bordered table-sm align-middle text-center">
    <thead class="table-light">
      <tr>
        <th>Nama</th>
        @if ($type !== 'yearly')
          @foreach ($dateRange as $d)
            <th>{{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
          @endforeach
        @endif
        <th>H</th><th>T</th><th>I</th><th>S</th><th>A</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($rekapData as $data)
        <tr>
          <td class="text-start">{{ $data['name'] }}</td>
          @if ($type !== 'yearly')
            @foreach ($data['statuses'] as $s)
              @php
                $bg = match($s){
                  'H'=>'bg-success-subtle',
                  'T'=>'bg-warning-subtle',
                  'I'=>'bg-info-subtle',
                  'S'=>'bg-primary-subtle',
                  'A'=>'bg-danger-subtle',
                  default=>'',
                };
              @endphp
              <td class="{{ $bg }}">{{ $s }}</td>
            @endforeach
          @endif
          <td>{{ $data['summary']['H'] }}</td>
          <td>{{ $data['summary']['T'] }}</td>
          <td>{{ $data['summary']['I'] }}</td>
          <td>{{ $data['summary']['S'] }}</td>
          <td>{{ $data['summary']['A'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ ($dateRange ? count($dateRange) : 0)+6 }}" class="text-muted text-center">Tidak ada data tersedia.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  @if($rekapData instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="mt-3 d-flex justify-content-center">
      {{ $rekapData->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div>
