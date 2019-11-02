/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { useFormGroup } from "@dashboard/forms/DashboardFormGroup";
import classNames from "classnames";
import { DashboardLabelType } from "@dashboard/forms/DashboardFormLabel";
import { TextareaAutosize } from "react-autosize-textarea/lib/TextareaAutosize";
import InputTextBlock from "@library/forms/InputTextBlock";

interface IProps extends React.InputHTMLAttributes<HTMLInputElement> {
    multiline?: boolean;
}

export const DashboardInput: React.FC<IProps> = (props: IProps) => {
    const { inputID, labelType } = useFormGroup();
    const classes = classNames("form-control", props.className);

    const rootClass = labelType === DashboardLabelType.WIDE ? "input-wrap-right" : "input-wrap";

    return (
        <div className={rootClass}>
            <InputTextBlock multiLineProps={{ rows: 4 }} inputProps={{ ...props }} margins={false} />
            {/* {props.multiLine ? (
                <TextareaAutosize {...restProps} id={inputID} className={classes} />
            ) : (
                <input type="text" {...restProps} id={inputID} className={classes} />
            )} */}
        </div>
    );
};
