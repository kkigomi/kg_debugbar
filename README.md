# 그누보드 Debugbar 플러그인

그누보드에 [PHP Debug Bar](http://phpdebugbar.com)를 추가하는 플러그인입니다.

"PHP Debug Bar"를 사용하여 그누보드의 실행 상태와 디버깅을 위한 기능을 제공합니다.

## 설치 및 활성화 방법

- 설치 경로: `/plugin/kg_debugbar`
- extend 파일 설치
  - 배포 파일 내 `_extend/_0_kg_debugbar.extend.php` 파일을 그누보드를 설치한 최상위 폴더의 `/extend` 폴더로 이동
  - extend 폴더에 파일이 없으면 이 플러그인은 동작할 수 없습니다

### 활성화 방법

PHP 상수를 정의하여 활성화 할 수 있습니다.

그누보드를 설치한 최상위 폴더에 `config.custom.php` 파일을 생성하여 다음과 같이 설정 할 수 있습니다.

- `KG_DEBUGBAR_ENABLE`
  - type: `bool`
  - default: `false`
  - Debugbar 활성화. 최고관리자에게만 활성화 됨
- `KG_DEBUGBAR_ENABLE_IP`
  - type: `string[]`
  - default: `[]`
  - 일치하는 IP에 대해 Debugbar 활성화
  - IP를 지정하는 경우 해당 IP에 **관리자 여부를 판단하지 않고** 실행된 DB 쿼리 등 민감한 데이터가 보여질 수 있으므로 주의하세요
  - 서버 설정의 문제로 `$_SERVER['REMOTE_ADDR']`에 접속자의 IP가 제대로 전달되지 않아 모두 동일 IP로 전달되는 등의 문제가 있다면 서버 문제를 수정하거나 **이 옵션을 사용하지 마세요**
- `KG_DEBUGBAR_DIR`
  - type: `string`
  - default: `'kg_debugbar'`
  - 플러그인의 폴더명
  - 기본 플러그인 설치 폴더와 다른 이름을 사용해 설치했을 때 해당 폴더명 지정 가능

설정 예시

```php
// 최고관리자 세션에서 Debugbar 활성화
define('KG_DEBUGBAR_ENABLE', true);

// 일치하는 IP에 Debugbar 항상 활성화
define('KG_DEBUGBAR_ENABLE_IP', ['127.0.0.1']);

// 'custom_debugbar' 폴더명으로 설치했을 때
define('KG_DEBUGBAR_DIR', 'custom_folder');
```

## 디버그 메시지 사용 방법 <sup>Since v0.2.0</sup>

### logger() <sup>이 함수는 0.4.0 버전(혹은 그 이후)에서 API 변경 및 이름이 변경될 예정입니다</sup>
`logger()` 메시지는 Debugbar의 'Messages' 패널에 표시됩니다.
`logger()`는 파라메터를 전달하지 않으면 PSR-3(Logger Interface) 규격을 따른 인터페이스를 반환합니다(PSR-3의 인터페이스를 반환하지만 보다 엄격하지 않음).

- emergency
- alert
- critical
- error
- warning
- notice
- info
- debug

```php
/**
 * `logger()`에 parameter를 지정하지 않으면 `Psr\Log\LoggerInterface`를 구현한 객체를 반환
 * @param mixed $message
 * @param mixed $context
 * @return void|Psr\Log\LoggerInterface
 */
logger('debug 타입 message');
logger()->error('error 타입 메시지');
logger()->info('info 타입 메시지');
logger()->emergency('emergency 타입 메시지');

logger('Hello {userName}', ['userName' => 'kkigomi']); // Hello kkigomi
logger()->error('Error: [{errorCode}] {errorMessage}', ['errorCode' => 404, 'errorMessage' => 'Not Found']); // Error: [404] Not Found

// 사용자 정의 라벨 지정
// [a-zA-Z0-9]
// 특별한 기능을 제공하지는 않으며 Debugbar에서 라벨을 선택해서 필터링 할 수 있음
logger()->log('custom_label', 'Message {foo}', ['foo' => 'bar']);
```

### dd() <sup>Since v0.2.0</sup>
`dd()` 함수가 사용된 위치에서 전달된 메시지를 출력 후 스크립트를 종료합니다. 스크립트를 종료하지 않으려면 `dump()`를 사용하세요.

```php
/**
 * 사용한 위치에서 메시지를 출력하고 스크립트를 종료
 * @param mixed $message
 * @return never
 */
dd('dd() message');
dd($member);
dd([$g5, $config]);
```

### dump() <sup>Since v0.2.0</sup>
`dump()` 함수가 사용된 위치에서 전달된 메시지를 출력합니다.

```php
/**
 * 사용한 위치에서 메시지를 출력
 * @param mixed $message
 * @return void
 */
dump('dump() message');
dump($member);
dump([$g5, $config]);
```


## License (LGPL-2.1-or-later)

Copyright (C) 2022 Kkigomi

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301
USA
