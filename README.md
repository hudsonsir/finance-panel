# 财务工具 - 续费管理系统

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net/)
[![ThinkPHP](https://img.shields.io/badge/ThinkPHP-6.0+-orange.svg)](https://www.thinkphp.cn/)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

一个基于 ThinkPHP后端的现代化财务续费管理系统，帮助您管理各种服务的续费提醒和财务记录。

## 功能特性

### 核心功能
- **产品管理**: 支持服务器、域名、其他类型的产品分类管理
- **续费记录**: 完整的续费历史记录和状态跟踪
- **财务统计**: 多币种财务报表和统计分析
- **到期提醒**: 自动化的到期提醒系统

### 通知系统
- **邮件通知**: 支持SMTP邮件提醒
- **Telegram机器人**: 支持Telegram消息推送
- **多通道配置**: 支持同时配置多个通知通道
- **定时任务**: 自动化到期提醒检查

### 财务功能
- **多币种支持**: CNY、USD、EUR等主流货币
- **汇率管理**: 支持自动汇率获取和手动配置
- **成本分析**: 按周期、类别进行费用分析
- **发票管理**: 账单和发票链接管理

## 快速开始

### 系统要求

- **PHP**: >= 7.4
- **数据库**: MySQL 5.7+
- **Web服务器**: Apache/Nginx

### 安装步骤

1. **克隆项目**
   ```bash
   git clone https://github.com/hudsonsir/hudsonsir-finance-panel.git
   cd finance-panel
   ```
    **安装依赖**
   ```bash
   composer install
   ```
2. **访问安装**
   ```
   域名/install
   ```

3. **完成安装**
   - 按照安装向导配置数据库连接
   - 创建管理员账号

##  使用指南

### 基本操作

1. **添加产品**
   - 进入管理后台 → 产品管理 → 添加产品
   - 填写产品信息、购买周期、预算等

2. **记录续费**
   - 选择产品 → 添加续费记录
   - 填写续费时间、金额、支付方式等

3. **配置通知**
   - 系统设置 → 邮件配置/Telegram配置
   - 设置SMTP信息或机器人Token

4. **查看统计**
   - 财务面板查看费用统计
   - 按时间、类别筛选数据

### 命令行工具

```bash
# 手动发送到期提醒
php think email:send-expire-reminder
```
```bash
# 定时任务
cd /www/wwwroot/finance-tool（替换成实际运行目录）
php think email:send-expire-reminder
```

## 配置说明

### 邮件配置
- 支持SMTP协议
- 可配置邮件模板支持HTML邮件格式

### Telegram配置
- 需要创建Telegram机器人
- 自定义消息模板

### 汇率配置
- 支持exchangerate-api接口
- 手动配置基准汇率
- 自动汇率更新

欢迎提交 Issue 和 Pull Request！

## 许可证

本项目采用 [GPL-3.0](LICENSE) 许可证开源。

##  致谢

- [ThinkPHP](https://www.thinkphp.cn/) - 优秀的PHP框架
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - 邮件发送库
- [Telegram Bot API](https://core.telegram.org/bots/api) - Telegram机器人接口

## 支持

如果您在使用过程中遇到问题，请：

1. 查看 [Issues](../../issues) 页面
2. 提交新的 Issue

---

**注意**: 请在生产环境中定期备份数据，保护敏感信息安全。
