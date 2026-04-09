<template>
  <div>
    <div class="page-header"><div><h2>Financial Reports</h2><p>Detailed financial analysis and exports</p></div>
      <div style="display:flex;gap:10px"><button class="btn btn-ghost">📥 Export PDF</button><button class="btn btn-blue">📊 Export Excel</button></div>
    </div>
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
      <div class="stat-card"><div class="stat-icon green">💰</div><div class="stat-value">₦42.8M</div><div class="stat-label">Total Revenue — 2nd Term</div></div>
      <div class="stat-card"><div class="stat-icon blue">📈</div><div class="stat-value">78%</div><div class="stat-label">Collection Rate</div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏳</div><div class="stat-value">₦12.1M</div><div class="stat-label">Outstanding Amount</div></div>
    </div>
    <div class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Revenue by Month</div></div>
        <Bar :data="monthlyChart" :options="barOpts" style="height:240px"/>
      </div>
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Collection Status</div></div>
        <Doughnut :data="statusChart" :options="doughnutOpts" style="height:240px"/>
      </div>
    </div>
    <div class="card" style="margin-top:20px">
      <div class="card-header"><div class="card-title">Fee Collection by Class</div></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Class</th><th>Students</th><th>Expected</th><th>Collected</th><th>Rate</th></tr></thead>
          <tbody>
            <tr v-for="c in classReport" :key="c.name">
              <td style="font-weight:500">{{ c.name }}</td><td>{{ c.students }}</td>
              <td>₦{{ Number(c.expected).toLocaleString() }}</td>
              <td style="color:var(--green2)">₦{{ Number(c.collected).toLocaleString() }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <div class="progress" style="width:80px"><div class="progress-bar" :style="{width:c.rate+'%',background:c.rate>=80?'var(--green)':c.rate>=60?'var(--yellow)':'var(--red)'}"></div></div>
                  <span style="font-size:12px">{{ c.rate }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { Bar, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale,LinearScale,BarElement,ArcElement,Tooltip,Legend)
const barOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8'}}},scales:{x:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}}},y:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11},callback:v=>'₦'+v/1e6+'M'}}}}
const doughnutOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:'#94a3b8',padding:14}}}}
const monthlyChart={labels:['Oct','Nov','Dec','Jan','Feb','Mar'],datasets:[{label:'Revenue',data:[8e6,12e6,3e6,18e6,24e6,42.8e6],backgroundColor:'rgba(16,185,129,0.6)',borderRadius:6}]}
const statusChart={labels:['Fully Paid (820)','Partial (284)','Unpaid (180)'],datasets:[{data:[820,284,180],backgroundColor:['#10b981','#f59e0b','#ef4444'],borderWidth:0}]}
const classReport=[
  {name:'JSS 1A',students:42,expected:3570000,collected:3213000,rate:90},
  {name:'JSS 2A',students:38,expected:3230000,collected:2907000,rate:90},
  {name:'JSS 3A',students:45,expected:3825000,collected:3060000,rate:80},
  {name:'SS 1B',students:35,expected:3220000,collected:2415000,rate:75},
  {name:'SS 2A',students:42,expected:3864000,collected:3478000,rate:90},
  {name:'SS 2B',students:40,expected:3680000,collected:2576000,rate:70},
  {name:'SS 3A',students:38,expected:3496000,collected:3496000,rate:100},
]
</script>
