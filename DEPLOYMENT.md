# QloApps Deployment Guide

This guide will help you deploy your QloApps hotel booking system to free hosting platforms.

## 🚂 Railway.app Deployment (Recommended)

Railway.app is the best free hosting option for QloApps as it provides:
- Free MySQL database
- PHP support
- Easy GitHub integration
- Persistent file storage

### Step 1: Prepare Your Repository

1. **Initialize Git repository** (if not already done):
```bash
git init
git add .
git commit -m "Initial commit - QloApps hotel booking system"
```

2. **Push to GitHub**:
   - Create a new repository on GitHub
   - Push your code:
```bash
git remote add origin https://github.com/yourusername/your-repo-name.git
git branch -M main
git push -u origin main
```

### Step 2: Deploy to Railway

1. **Visit Railway.app**:
   - Go to [railway.app](https://railway.app)
   - Sign up with your GitHub account

2. **Create New Project**:
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your QloApps repository

3. **Important - Force Docker Build**:
   - Go to your project **Settings** → **Environment**
   - Add this environment variable:
     - `RAILWAY_DOCKERFILE_PATH`: `Dockerfile`
   - This ensures Railway uses Docker instead of auto-detection

4. **Add MySQL Database**:
   - In your Railway project dashboard
   - Click "New" → "Database" → "Add MySQL"
   - Railway will automatically create a MySQL database

5. **Environment Variables**:
   Railway will automatically set database environment variables. You can add custom ones:
   - `PS_COOKIE_KEY`: Generate a random 32-character string
   - `PS_COOKIE_IV`: Generate a random 8-character string
   - `PS_RIJNDAEL_KEY`: Generate a random 32-character string
   - `PS_RIJNDAEL_IV`: Generate a random 16-character string

6. **Deploy**:
   - Railway will automatically build and deploy your application using Docker
   - Build time: 5-10 minutes for first deployment
   - You'll get a public URL like: `your-app-name.up.railway.app`

### Step 3: Initial Setup

1. **Access Your Site**:
   - Visit your Railway URL
   - You should see the QloApps installer

2. **Run Installation**:
   - Follow the QloApps installation wizard
   - Database details will be auto-filled from Railway environment variables
   - Complete the setup process

## 🎨 Render.com Deployment (Alternative)

### Step 1: Create Render Account
- Visit [render.com](https://render.com)
- Sign up with GitHub

### Step 2: Create Web Service
- Click "New" → "Web Service"
- Connect your GitHub repository
- Use these settings:
  - **Build Command**: `chmod +x deploy.sh && ./deploy.sh`
  - **Start Command**: `apache2-foreground`
  - **Dockerfile Path**: `Dockerfile`

### Step 3: Add Database
- Create a PostgreSQL database on Render
- You'll need to modify QloApps to work with PostgreSQL (requires code changes)

## 🔧 Manual Setup Instructions

If you need to set up the application manually:

1. **Set Database Configuration**:
   Edit `config/settings.inc.php` with your database details

2. **Set Permissions**:
```bash
chmod -R 777 cache/
chmod -R 777 log/
chmod -R 777 upload/
chmod -R 777 download/
chmod -R 777 img/
```

3. **Install Dependencies**:
```bash
composer install
```

## 🔐 Security Configuration

### Environment Variables Required:
- `MYSQL_HOST`: Database host
- `MYSQL_PORT`: Database port (3306)
- `MYSQL_DATABASE`: Database name
- `MYSQL_USER`: Database username
- `MYSQL_PASSWORD`: Database password
- `PS_COOKIE_KEY`: Random 32-character string
- `PS_COOKIE_IV`: Random 8-character string
- `PS_RIJNDAEL_KEY`: Random 32-character string
- `PS_RIJNDAEL_IV`: Random 16-character string

### Generate Random Keys:
You can generate random keys using online tools or:
```bash
# For Linux/Mac
openssl rand -hex 16  # For 32-character key
openssl rand -hex 4   # For 8-character IV
openssl rand -hex 8   # For 16-character IV
```

## 📝 Post-Deployment Checklist

- [ ] Database connection working
- [ ] Admin panel accessible at `/admin955dxqibz/`
- [ ] File uploads working
- [ ] Email configuration set up
- [ ] SSL certificate configured (if available)
- [ ] Backup strategy implemented

## 🆘 Troubleshooting

### Common Issues:

1. **500 Internal Server Error**:
   - Check file permissions
   - Verify database connection
   - Check PHP error logs

2. **Database Connection Failed**:
   - Verify environment variables
   - Check database credentials
   - Ensure database is running

3. **Upload Issues**:
   - Check directory permissions
   - Verify PHP upload limits
   - Check available disk space

### Support Resources:
- [QloApps Documentation](https://docs.qloapps.com/)
- [QloApps Forum](https://forums.qloapps.com/)
- [Railway Documentation](https://docs.railway.app/)

## 📞 Need Help?

If you encounter issues during deployment, check:
1. Railway/Render build logs
2. Application error logs
3. Database connection status
4. File permissions

Good luck with your QloApps hotel booking system deployment! 🏨✨
