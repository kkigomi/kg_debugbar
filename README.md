# 그누보드 PHP Debug Bar 플러그인
그누보드에 [PHP Debug Bar](http://phpdebugbar.com)를 추가하는 플러그인입니다.

"PHP Debug Bar"를 사용하여 그누보드의 실행 상태와 디버깅을 위한 기능을 제공합니다.

## 설치 및 활성화 방법
- 설치 경로: `/plugin/kg_phpdebugbar`
- extend 파일 설치
  - 배포 파일 내 `_extend/_0_kg_debugbar.extend.php` 파일을 그누보드를 설치한 최상위 폴더의 `/extend` 폴더로 이동
  - extend 폴더에 파일이 없으면 이 플러그인은 동작할 수 없습니다

### 활성화 방법
PHP 상수를 정의하여 활성화 할 수 있습니다.

그누보드를 설치한 최상위 폴더에 `config.custom.php` 파일을 생성하여 다음과 같이 설정 할 수 있습니다.

- `KG_DEBUGBAR_ENABLE`
  - type: `bool`
  - PHP DebugBar 활성화. 최고관리자에게만 활성화 됨
- `KG_DEBUGBAR_ENABLE_IP`
  - type: `array<string>`
  - 일치하는 IP에 대해 PHP DebugBar 활성화
  - 관리자 여부 상관 없음
  - IP를 지정하는 경우 해당 IP에 관리자 여부를 판단하지 않고 실행된 쿼리 목록 등의 민감한 데이터가 보여질 수 있으므로 주의하세요
  - 서버 설정의 문제로 `$_SERVER['REMOTE_ADDR']`에 접속자의 IP가 제대로 전달되지 않아 모두 동일 IP로 전달되는 등의 문제가 있다면 서버 문제를 수정하거나 이 옵션을 사용하지 마시기 바랍니다.
- `KG_DEBUGBAR_DIR`
  - type: `string`
  - 플러그인의 폴더명
  - 기본 플러그인 설치 폴더와 다른 이름을 사용해 설치했을 때 해당 폴더명 지정 가능

설정 예시
```php
// define('KG_DEBUGBAR_ENABLE', true);
// define('KG_DEBUGBAR_ENABLE_IP', ['127.0.0.1']);
// define('KG_DEBUGBAR_DIR', 'custom_folder');
```

## License (LGPL-2.1)
Copyright (C) 2022  Kkigomi

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301
USA
