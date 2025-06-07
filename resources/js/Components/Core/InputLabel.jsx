export default function InputLabel({
    value,
    className = '',
    children,
    ...props
}) {
    return (
        <label
            {...props}
            className={
                `label text-black` +
                className
            }
        >
            <span className={'label-text'}>{value ? value : children}</span>
        </label>
    );
}
