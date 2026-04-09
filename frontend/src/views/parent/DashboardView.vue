<template>
  <div>
    <div class="page-header"><div><h2>Parent Dashboard 👪</h2><p>Tracking your child's academic progress</p></div></div>

    <!-- Child Banner -->
    <div class="child-banner">
      <div class="avatar avatar-lg">MC</div>
      <div style="flex:1">
        <div style="font-size:20px;font-weight:700">Michael Chen</div>
        <div style="font-size:13px;color:var(--text2);margin-top:4px">SS 2A · Admission: GFA/2023/042 · 2nd Term 2025/2026</div>
      </div>
      <div style="display:flex;gap:24px;text-align:center;flex-wrap:wrap">
        <div><div style="font-size:22px;font-weight:800;color:var(--accent2)">3rd</div><div style="font-size:11px;color:var(--text3)">Class Position</div></div>
        <div><div style="font-size:22px;font-weight:800;color:var(--green2)">95%</div><div style="font-size:11px;color:var(--text3)">Attendance</div></div>
        <div><div style="font-size:22px;font-weight:800;color:var(--yellow)">₦0</div><div style="font-size:11px;color:var(--text3)">Fee Balance</div></div>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon blue">📊</div><div class="stat-value">90.8%</div><div class="stat-label">Term Average</div><div class="stat-change up">↑ Excellent</div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">95.4%</div><div class="stat-label">Attendance Rate</div></div>
      <div class="stat-card"><div class="stat-icon yellow">📝</div><div class="stat-value">2</div><div class="stat-label">Upcoming Exams</div></div>
      <div class="stat-card"><div class="stat-icon purple">💰</div><div class="stat-value">Paid</div><div class="stat-label">Fee Status</div></div>
    </div>

    <div class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-header">
          <div class="card-title">Recent Results</div>
          <router-link to="/parent/report-card" class="btn btn-ghost btn-sm">Full Report Card</router-link>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Subject</th><th>Score</th><th>Grade</th></tr></thead>
            <tbody>
              <tr v-for="r in results" :key="r.subject">
                <td>{{ r.subject }}</td>
                <td>{{ r.total }}/100</td>
                <td><span :class="['badge',r.grade==='A'?'badge-success':r.grade==='B'?'badge-info':'badge-warning']">{{ r.grade }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Recent Notifications</div></div>
        <div v-for="n in notifications" :key="n.title" style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
          <div style="width:32px;height:32px;border-radius:8px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">{{ n.icon }}</div>
          <div>
            <div style="font-size:12px;font-weight:600">{{ n.title }}</div>
            <div style="font-size:11px;color:var(--text3);margin-top:2px">{{ n.time }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Announcements -->
    <div class="card" style="margin-top:20px">
      <div class="card-header"><div class="card-title">School Announcements</div><router-link to="/parent/announcements" class="btn btn-ghost btn-sm">View All</router-link></div>
      <div v-for="a in announcements" :key="a.title" style="padding:14px;border-radius:10px;border-left:3px solid var(--accent);background:rgba(59,130,246,0.05);margin-bottom:10px">
        <div style="font-size:14px;font-weight:600;margin-bottom:4px">{{ a.title }}</div>
        <div style="font-size:12px;color:var(--text2);line-height:1.5">{{ a.body }}</div>
        <div style="font-size:11px;color:var(--text3);margin-top:6px">{{ a.date }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
const results = [
  { subject:'Mathematics', total:90, grade:'A' },
  { subject:'English', total:79, grade:'B' },
  { subject:'Physics', total:80, grade:'A' },
  { subject:'Chemistry', total:74, grade:'B' },
  { subject:'Biology', total:93, grade:'A' },
]
const notifications = [
  { icon:'📝', title:'Mathematics Exam Scheduled — March 20', time:'2 hours ago' },
  { icon:'💰', title:'Fee Payment Confirmed for 2nd Term', time:'2 days ago' },
  { icon:'📊', title:'2nd Term Results Now Available', time:'3 days ago' },
  { icon:'📢', title:'Parent-Teacher Conference — March 22', time:'5 days ago' },
]
const announcements = [
  { title:'3rd Term Examination Schedule Released', body:'Exams begin April 14, 2026. All students should collect printed timetables from the academic office.', date:'March 15, 2026' },
  { title:'Sports Day & Inter-House Competition', body:'Annual Sports Day is scheduled for Saturday, April 5, 2026. All students are expected to participate.', date:'March 14, 2026' },
]
</script>
<style scoped>
.child-banner { background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(139,92,246,0.1)); border:1px solid rgba(59,130,246,0.2); border-radius:14px; padding:24px; display:flex; align-items:center; gap:20px; margin-bottom:24px; flex-wrap:wrap; }
</style>
