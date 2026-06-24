# Production Go-Live Checklist

## Application

```txt
[ ] APP_ENV=production
[ ] APP_DEBUG=false
[ ] APP_KEY generated and backed up securely
[ ] APP_URL uses HTTPS domain
[ ] config/cache/routes/views optimized
[ ] storage symlink created
[ ] file permissions reviewed
```

## Database/cache

```txt
[ ] MySQL database created
[ ] MySQL user has least required permissions
[ ] migrations run
[ ] backups scheduled
[ ] Redis installed and password/firewall configured
[ ] queue/session/cache use Redis
```

## Workers

```txt
[ ] Horizon supervised
[ ] Reverb supervised
[ ] Scheduler cron installed
[ ] Failed jobs monitored
[ ] Logs rotated
```

## Payment

```txt
[ ] Stripe test mode passed
[ ] Stripe webhook endpoint configured
[ ] Stripe webhook secret added
[ ] Duplicate webhook handling tested
[ ] Refund/failure process documented
[ ] Live mode approved before enabling
```

## Flight provider

```txt
[ ] Duffel/Amadeus sandbox passed
[ ] Production API credentials approved
[ ] Offer revalidation tested
[ ] Failed finalization process tested
[ ] Real order finalization intentionally enabled only after sign-off
```

## Security

```txt
[ ] HTTPS forced
[ ] Admin users reviewed
[ ] Admin roles reviewed
[ ] Rate limiters enabled
[ ] Security headers enabled
[ ] Sensitive logs masked
[ ] Health endpoint restricted/monitored
[ ] Provider/Stripe secrets not exposed to frontend
```

## Final decision

```txt
[ ] Go live approved
[ ] Rollback plan ready
[ ] Backup verified
[ ] Support contact assigned
```
