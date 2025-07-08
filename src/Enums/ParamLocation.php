<?php
namespace Ahmedsalheia\Docsy\Enums;
enum ParamLocation: string {
    case Query = 'query';
    case Path = 'path';
    case Header = 'header';
    case Cookie = 'cookie';
    case Body = 'body';
}