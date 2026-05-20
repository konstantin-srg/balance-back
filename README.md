Server Start
```bash
docker compose -f ./deploy/compose.yml -p balance-back up -d
echo Server http://localhost:90/
echo PhpMyAdmin http://localhost:1500/
```

Push
```bash
powershell ./deploy/push.sh
```

```
Deploy
sh deploy-front.sh
sh deploy-back.sh
```
