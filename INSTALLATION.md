# ShelterAgent Dashboard - Installation & Usage Guide

## 🚀 Quick Start

### 1. Setup Database

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shelter_agent
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create database:
```bash
# MySQL
mysql -u root -p
CREATE DATABASE shelter_agent;
exit;
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. (Optional) Seed Dummy Data

To test with realistic data (5 agents, 7 days of metrics):

```bash
php artisan db:seed --class=DummyDataSeeder
```

### 4. Start Server

```bash
php artisan serve
```

Dashboard: http://localhost:8000
API: http://localhost:8000/api

## 📱 Using the Dashboard

### Main Dashboard (/)
- View all registered agents
- See online/offline status
- Quick stats overview
- Click "View Details" to see individual agent metrics

### Agent Detail Page (/agent/{id})
- Real-time metrics (auto-refreshes every 10s)
- Historical charts (CPU, Memory, Disk, Network)
- Service monitoring table
- System information

## 🔌 Connecting Python Agents

### On Agent Server

```bash
cd python-agent
pip install -r requirements.txt

# Configure
cp .env.example .env
nano .env  # Set SERVER_URL=http://your-dashboard-url/api

# Run agent (auto-registers on first run)
python agent.py

# Or as service
sudo systemctl enable shelteragent
sudo systemctl start shelteragent
```

Agent will:
1. Auto-register with dashboard
2. Start sending metrics every 30s
3. Update services every 60s
4. Send heartbeat every 10s

## 📊 API Endpoints

All endpoints under `/api`:

### Public Endpoints
- `GET /health` - Health check

### Agent Endpoints
- `POST /agent/register` - Register new agent
- `POST /agent/heartbeat` - Send heartbeat
- `GET /agents` - List all agents
- `GET /agents/{id}` - Get agent details

### Metrics Endpoints
- `POST /metrics` - Submit metrics (requires API token)
- `GET /metrics/{agentId}` - Get historical metrics
- `GET /metrics/{agentId}/realtime` - Get current metrics
- `GET /metrics/{agentId}/snapshots` - Get aggregated stats

### Services Endpoints
- `POST /services` - Submit services data (requires API token)
- `GET /services/{agentId}` - Get services list
- `GET /services/{agentId}/top` - Get top resource consumers

## 🎨 Dashboard Features

### ✅ Implemented
- [x] Modern Tailwind CSS UI
- [x] Agent status monitoring
- [x] Real-time metrics display
- [x] Interactive Chart.js graphs
- [x] Service monitoring table
- [x] Auto-refresh (dashboard: 30s, agent detail: 10s)
- [x] Responsive design
- [x] Alpine.js for interactivity

### 🚧 TODO
- [ ] SSH terminal integration
- [ ] Alert notifications
- [ ] Multi-user authentication
- [ ] Configuration management
- [ ] Export to PDF/CSV

## 🔧 Troubleshooting

### "No agents found"
- Run `php artisan db:seed --class=DummyDataSeeder` for test data
- Or start a Python agent to register automatically

### Charts not showing
- Check browser console for JavaScript errors
- Ensure agent has metrics data (wait 30s after registration)
- Verify API endpoints return data: `/api/metrics/{id}?hours=1`

### API returns 404
- Clear route cache: `php artisan route:clear`
- Check `routes/api.php` exists
- Verify API routes: `php artisan route:list`

### CORS errors
- Ensure CorsMiddleware is registered
- Check browser console for specific error
- API should be accessible from same origin

## 📈 Performance Tips

1. **Database Optimization**
   ```bash
   # Add indexes if needed
   php artisan migrate
   ```

2. **Cache Configuration**
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

3. **Queue Workers** (for production)
   ```bash
   php artisan queue:work
   ```

## 🔐 Security Recommendations

### Production Checklist
- [ ] Change `APP_ENV=production` in `.env`
- [ ] Set secure `APP_KEY` (run `php artisan key:generate`)
- [ ] Enable HTTPS/SSL
- [ ] Restrict CORS to specific domains
- [ ] Add rate limiting to API endpoints
- [ ] Implement user authentication
- [ ] Secure API tokens with encryption
- [ ] Regular database backups

## 📚 Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Tailwind CSS 3 (CDN)
- **Charts**: Chart.js 4
- **Interactivity**: Alpine.js 3
- **Database**: MySQL 8+
- **Python Agent**: Python 3.7+ with psutil

## 🎯 Next Steps

1. **Deploy to Production**
   - Use Laravel Forge, DigitalOcean, or AWS
   - Configure SSL certificate
   - Set up monitoring and backups

2. **Add More Agents**
   - Install Python agent on each server
   - Agents auto-register on first run

3. **Customize Dashboard**
   - Modify views in `resources/views/dashboard/`
   - Add custom metrics or charts
   - Implement alerting system

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review API responses in browser DevTools
- Verify Python agent logs: `python-agent/agent.log`

---

**Happy Monitoring! 🚀**
