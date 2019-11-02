/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { styleFactory } from "@library/styles/styleUtils";

interface IProps {
    children?: React.ReactNode;
    allowOverflow?: boolean;
}

const style = styleFactory("tableCell");

export function DashboardTableCell(props: IProps) {
    const rootClass = style({
        overflow: props.allowOverflow ? "visible" : "hidden",
    });

    return <td className={rootClass}>{props.children}</td>;
}
