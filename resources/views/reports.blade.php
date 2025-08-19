@extends('layouts.master')
@section('title','Báo cáo tổng quan')

@section('content')
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card card-stat p-3 text-center"><h5>{{ $summary['total'] }}</h5><p>Tổng công việc</p></div></div>
  <div class="col-md-3"><div class="card card-stat p-3 text-center"><h5 class="text-success">{{ $summary['done'] }}</h5><p>Hoàn thành</p></div></div>
  <div class="col-md-3"><div class="card card-stat p-3 text-center"><h5 class="text-primary">{{ $summary['doing'] }}</h5><p>Đang làm</p></div></div>
  <div class="col-md-3"><div class="card card-stat p-3 text-center"><h5 class="text-danger">{{ $summary['overdue'] }}</h5><p>Trễ hạn</p></div></div>
</div>

<div class="card p-3 mb-3">
  <h6 class="mb-3">Phân bổ trạng thái công việc</h6>
  <canvas id="statusDonut" height="140"></canvas>
</div>

<div class="card p-3 mb-3">
  <h6 class="mb-3">Tiến độ hoàn thành theo tuần</h6>
  <canvas id="weeklyLine" height="140"></canvas>
</div>

<div class="card p-3">
  <h6 class="mb-3">Phân tích theo phòng ban</h6>
  <canvas id="deptBar" height="140"></canvas>
</div>

@push('scripts')
<script>
const donut = new Chart(document.getElementById('statusDonut'),{
  type:'doughnut',
  data:{labels:['Hoàn thành','Đang làm','Trễ hạn','Chưa bắt đầu'],
        datasets:[{data:[{{ $summary['done'] }},{{ $summary['doing'] }},{{ $summary['overdue'] }},{{ $summary['todo'] }}]}]}
});

const weekly = new Chart(document.getElementById('weeklyLine'),{
  type:'line',
  data:{labels: @json($weekly['labels']),
        datasets:[{label:'Hoàn thành', data:@json($weekly['values']), fill:true, tension:.3}]}
});

const dept = new Chart(document.getElementById('deptBar'),{
  type:'bar',
  data:{labels: @json(array_keys($byDept)),
        datasets:[{label:'Số việc', data:@json(array_values($byDept))}]}
});
</script>
@endpush
@endsection
