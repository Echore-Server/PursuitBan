# PursuitBan

基本的なクライアントデータ参照BANを行えるほか、<br>
**検証**、 **実行**、 **解除**、**データ提供** を拡張できる総合的なBANプラグイン

## 検証の拡張

そのプレイヤーがBANされているかどうかを確認する<br>

- `PursuitJudger` を継承し `PursuitBan->registerJudger` で登録

### ビルトイン: ClientDataIntersects

クライアントデータ、信頼レベルを参照し、どれかの項目が一致していれば BAN されていると判断する

## 実行の拡張

BAN したときに起こすアクション<br>

- `PursuitExecutor` を継承し `PursuitBan->registerExecutor` で登録

### ビルトイン: Kick

全ての Executor が終了後、プレイヤーがオンラインならキックする

## 解除の拡張

BAN を解除するときに起こすアクション<br>
ビルトインはありません

- `PursuitRevoker` を継承し `PursuitBan->registerRevoker` で登録

## データ提供の拡張

BAN する前、対象プレイヤーのクライアントデータを提供する<br>
十分に提供されなかった場合、BAN は失敗します

- `PursuitProvider` を継承し `PursuitBan->registerProvider` で登録

### ビルトイン: OnlinePlayer

対象プレイヤーがオンラインなら、セッションから提供する
